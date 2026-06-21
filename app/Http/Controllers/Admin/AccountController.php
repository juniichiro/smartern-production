<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportAccountsRequest;
use App\Http\Requests\Admin\StoreAccountRequest;
use App\Http\Requests\Admin\UpdateAccountRequest;
use App\Mail\UserAccountCreatedMail;
use App\Models\InternDetail;
use App\Models\Office;
use App\Models\Role;
use App\Models\SchoolCoordinatorDetail;
use App\Models\SupervisorDetail;
use App\Models\User;
use App\Services\SecurityAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\OnboardingApplicant;
use App\Models\MatchResult;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    protected $auditService;

    public function __construct(SecurityAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = User::with(['role', 'internDetails.office', 'supervisorDetails.office', 'schoolCoordinatorDetails']);

        // Server-side search logic
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhereHas('internDetails', function ($inner) use ($search) {
                        $inner->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supervisorDetails', function ($inner) use ($search) {
                        $inner->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Reverted to oldest() to show the first users created on the first page
        $users = $query->oldest()->paginate(10)->withQueryString();

        $offices = Cache::remember('all_offices', 3600, function () {
            return Office::all();
        });

        $roles = Cache::remember('all_roles', 3600, function () {
            return Role::all();
        });

        $hasHrHead = SupervisorDetail::where('is_hr_head', true)->exists() || User::where('role_id', UserRole::HEAD_SUPERVISOR)->exists();

        $pendingOnboarding = OnboardingApplicant::select('onboarding_applicants.*', 'match_results.office_id', 'offices.name as office_name')
            ->join('match_results', 'match_results.applicant_name', '=', 'onboarding_applicants.full_name')
            ->join('offices', 'offices.id', '=', 'match_results.office_id')
            ->where('match_results.is_confirmed', true)
            ->whereNotIn('onboarding_applicants.email', function ($q) {
                $q->select('email')->from('users');
            })
            ->get();

        return view('admin.accounts', compact('users', 'offices', 'roles', 'hasHrHead', 'pendingOnboarding'));
    }

    public function store(StoreAccountRequest $request)
    {
        $roleId = $request->input('role_id');
        $role = Role::findOrFail($roleId);
        $roleName = $role->name;

        if ($roleName === 'admin') {
            return redirect()->back()->withErrors(['role_id' => 'Unauthorized role assignment.'])->withInput();
        }

        try {
            return DB::transaction(function () use ($request, $role, $roleName) {

                $finalRoleId = $role->id;
                // Handle Head Supervisor Role ID swap
                if ($roleName === 'supervisor' && $request->boolean('is_hr_head')) {
                    $finalRoleId = UserRole::HEAD_SUPERVISOR->value;
                }

                $plainPassword = Str::password(12, symbols: true);

                $user = User::create([
                    'email' => $request->input('email'),
                    'role_id' => $finalRoleId,
                    'password' => Hash::make($plainPassword),
                    'google2fa_secret' => null,
                    'google2fa_enabled' => false,
                    'is_first_login' => true,
                    'failed_attempts' => 0,
                ]);
                $this->saveProfile($user, $roleName, $request);

                try {
                    Mail::to($user->email)->send(new UserAccountCreatedMail($user, $plainPassword));
                } catch (\Exception $e) {
                    Log::error("Failed to send onboarding email to {$user->email}: ".$e->getMessage());
                }

                $this->auditService->log('Account Created', "User: {$request->email} as {$roleName}");

                return redirect()->route('admin.account')->with('success', 'Account finalized & published.');
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Creation failed: '.$e->getMessage()])->withInput();
        }
    }

    public function importCsv(ImportAccountsRequest $request)
    {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        fgetcsv($handle); // Skip header

        $count = 0;

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle)) !== false) {
                // CSV Mapping:
                // 0:first, 1:middle, 2:last, 3:email, 4:school, 5:office_id, 6:role_id
                // 7:gender, 8:gender_other, 9:phone, 10:picture, 11:emergency
                if (count($data) < 7) {
                    continue;
                }

                $email = $data[3];
                if (User::where('email', $email)->exists()) {
                    continue;
                }

                $roleId = $data[6];
                $role = Role::find($roleId);
                if (! $role || $role->name === 'admin') {
                    continue;
                }

                $plainPassword = Str::password(12, symbols: true);
                $user = User::create([
                    'email' => $email,
                    'password' => Hash::make($plainPassword),
                    'role_id' => $roleId,
                    'google2fa_secret' => null,
                    'google2fa_enabled' => false,
                    'failed_attempts' => 0,
                    'is_first_login' => true,
                ]);
                $profileData = [
                    'user_id' => $user->id,
                    'first_name' => ! empty($data[0]) ? $data[0] : 'Intern',
                    'middle_name' => $data[1] ?? '',
                    'last_name' => ! empty($data[2]) ? $data[2] : 'Account',
                    'phone_number' => ! empty($data[9]) ? $data[9] : '09000000000',
                    'profile_picture' => $data[10] ?? '',
                ];

                if ($role->name === 'intern') {
                    InternDetail::create(array_merge($profileData, [
                        'school' => ! empty($data[4]) ? $data[4] : 'N/A',
                        'office_id' => $data[5],
                        'gender' => (! empty($data[7]) && in_array(ucfirst($data[7]), ['Male', 'Female', 'Other'])) ? ucfirst($data[7]) : 'Male',
                        'gender_other_specify' => $data[8] ?? '',
                        'emergency_contact' => $data[11] ?? '',
                        'course' => 'N/A',
                        'hours_rendered' => 0,
                    ]));
                } elseif ($role->name === 'supervisor' || $roleId == UserRole::HEAD_SUPERVISOR->value) {
                    SupervisorDetail::create(array_merge($profileData, [
                        'office_id' => $data[5],
                        'is_hr_head' => ($roleId == UserRole::HEAD_SUPERVISOR->value),
                    ]));
                } elseif ($role->name === 'school_coordinator') {
                    SchoolCoordinatorDetail::create(array_merge($profileData, [
                        'school' => ! empty($data[4]) ? $data[4] : 'N/A',
                    ]));
                }

                try {
                    Mail::to($user->email)->send(new UserAccountCreatedMail($user, $plainPassword));
                } catch (\Exception $e) {
                    Log::error("Failed to send bulk onboarding email to {$user->email}: ".$e->getMessage());
                }

                $count++;
            }

            DB::commit();
            fclose($handle);
            $this->auditService->log('Bulk Import', "Imported {$count} accounts via CSV");

            return back()->with('success', "Successfully imported $count accounts.");

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($handle)) {
                fclose($handle);
            }

            return back()->withErrors(['error' => 'Import Error: '.$e->getMessage()]);
        }
    }

    public function update(UpdateAccountRequest $request, User $user)
    {
        try {
            return DB::transaction(function () use ($request, $user) {
                $user->update(['email' => $request->email]);

                // Handle Role ID Swap for Supervisor <-> Head Supervisor
                $currentRoleName = $user->role->name;
                $newRoleName = $currentRoleName;

                if ($currentRoleName === 'supervisor' || $currentRoleName === 'head_supervisor') {
                    $isHrHeadRequested = $request->boolean('is_hr_head');

                    if ($isHrHeadRequested) {
                        $user->role_id = UserRole::HEAD_SUPERVISOR;
                        $newRoleName = 'head_supervisor';
                    } else {
                        $user->role_id = UserRole::SUPERVISOR;
                        $newRoleName = 'supervisor';
                    }
                    $user->save();
                }

                $details = $user->internDetails ?? $user->supervisorDetails ?? $user->schoolCoordinatorDetails;
                $existingPath = $details ? $details->profile_picture : null;

                $this->saveProfile($user, $newRoleName, $request, $existingPath);

                $this->auditService->log('Account Updated', "User #{$user->id}: {$request->email}");

                return redirect()->route('admin.account')->with('success', 'Account details refined.');
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Update failed: '.$e->getMessage()])->withInput();
        }
    }

    private function saveProfile($user, $roleName, $request, $existingPath = null)
    {
        $imagePath = $existingPath;

        if ($request->hasFile('profile_picture')) {
            // Delete old picture if exists
            if ($existingPath) {
                Storage::disk('public')->delete($existingPath);
            }

            // Store in 'profiles' folder on 'public' disk (storage/app/public/profiles)
            $imagePath = $request->file('profile_picture')->store('profiles', 'public');
        }

        $profileData = [
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'school' => $request->school ?? 'N/A',
            'office_id' => $request->office_id,
            'profile_picture' => $imagePath,
        ];

        if ($roleName === 'intern') {
            $profileData['gender'] = $request->gender;
            $profileData['gender_other_specify'] = $request->gender_other_specify;
            $profileData['emergency_contact'] = $request->emergency_contact;
            $profileData['course'] = 'N/A';
            $profileData['hours_rendered'] = 0;
            InternDetail::updateOrCreate(['user_id' => $user->id], $profileData);
        } elseif ($roleName === 'supervisor' || $roleName === 'head_supervisor') {
            $profileData['is_hr_head'] = ($roleName === 'head_supervisor' || $request->boolean('is_hr_head'));
            SupervisorDetail::updateOrCreate(['user_id' => $user->id], $profileData);
        } elseif ($roleName === 'school_coordinator') {
            SchoolCoordinatorDetail::updateOrCreate(['user_id' => $user->id], $profileData);
        }
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot delete yourself.']);
        }

        $details = $user->internDetails ?? $user->supervisorDetails ?? $user->schoolCoordinatorDetails;

        if ($details && $details->profile_picture) {
            $fullPath = public_path($details->profile_picture);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }

        $email = $user->email;
        $user->delete();
        $this->auditService->log('Account Deleted', "User: {$email}");

        return redirect()->route('admin.account')->with('success', 'Account withdrawn.');
    }

    public function approveOnboardingAccount(Request $request, $id)
    {
        $applicant = OnboardingApplicant::findOrFail($id);
        
        $match = MatchResult::where('applicant_name', $applicant->full_name)
            ->where('is_confirmed', true)
            ->first();
            
        if (! $match) {
            return redirect()->back()->with('error', 'No finalized office assignment found for this applicant.');
        }
        
        if (User::where('email', $applicant->email)->exists()) {
            return redirect()->back()->with('error', 'An account with this email address already exists.');
        }

        $internRole = Role::where('name', 'intern')->first();
        if (! $internRole) {
            return redirect()->back()->with('error', 'Intern role not found in system.');
        }
        
        try {
            DB::transaction(function () use ($applicant, $match, $internRole) {
                $plainPassword = Str::password(12, symbols: true);
                
                $user = User::create([
                    'email' => $applicant->email,
                    'password' => Hash::make($plainPassword),
                    'role_id' => $internRole->id,
                    'google2fa_secret' => null,
                    'google2fa_enabled' => false,
                    'failed_attempts' => 0,
                    'is_first_login' => true,
                ]);
                
                InternDetail::create([
                    'user_id' => $user->id,
                    'first_name' => $applicant->first_name,
                    'middle_name' => $applicant->middle_name ?? '',
                    'last_name' => $applicant->last_name,
                    'phone_number' => $applicant->phone_number ?? '09000000000',
                    'profile_picture' => $applicant->profile_picture ?? '',
                    'school' => $applicant->university ?? 'N/A',
                    'office_id' => $match->office_id,
                    'gender' => (! empty($applicant->gender) && in_array(ucfirst($applicant->gender), ['Male', 'Female', 'Other'])) ? ucfirst($applicant->gender) : 'Male',
                    'gender_other_specify' => $applicant->gender_other_specify ?? '',
                    'emergency_contact' => $applicant->emergency_contact ?? '',
                    'course' => 'N/A',
                    'hours_rendered' => 0,
                ]);
                
                try {
                    Mail::to($user->email)->send(new UserAccountCreatedMail($user, $plainPassword));
                } catch (\Exception $e) {
                    Log::error("Failed to send onboarding email to {$user->email}: ".$e->getMessage());
                }
                
                $this->auditService->log('Account Created', "Approved onboarding applicant: {$applicant->email} as intern");
            });
            
            return redirect()->back()->with('success', 'Onboarding account approved and created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Approve failed: ' . $e->getMessage()]);
        }
    }

    public function discardOnboardingAccount(Request $request, $id)
    {
        $applicant = OnboardingApplicant::findOrFail($id);
        $email = $applicant->email;
        
        DB::transaction(function () use ($applicant) {
            MatchResult::where('applicant_name', $applicant->full_name)->delete();
            $applicant->delete();
        });
        
        $this->auditService->log('Account Request Discarded', "Discarded onboarding application for: {$email}");
        
        return redirect()->back()->with('success', 'Onboarding application discarded.');
    }
}
