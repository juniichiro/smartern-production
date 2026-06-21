@props([
    'user' => null,
    'size' => 10, // Default tailwind size (w-10 h-10)
    'border' => 2,
    'textSize' => 'xs',
    'circular' => true,
    'background' => 'bg-[#084577] dark:bg-gray-700'
])

@php
    // 1. Resolve Details Object
    $details = null;
    $firstName = 'SmarTern';
    $lastName = 'User';
    $profilePic = null;

    if ($user instanceof \App\Models\User) {
        $details = $user->internDetails 
                  ?? $user->supervisorDetails 
                  ?? $user->schoolCoordinatorDetails;

        if ($details) {
            $firstName = $details->first_name ?? 'User';
            $lastName = $details->last_name ?? '';
            $profilePic = $details->profile_picture ?? null;
        } else {
            // Special fallback for Admin or users without profiles
            $firstName = ($user->role && $user->role->name === 'admin') ? 'SmarTern' : 'User';
            $lastName = ($user->role && $user->role->name === 'admin') ? 'Admin' : '';
        }
    } else {
        // Assume the object passed IS a details-like object (InternDetail, etc)
        $firstName = $user->first_name ?? 'User';
        $lastName = $user->last_name ?? '';
        $profilePic = $user->profile_picture ?? null;
    }

    // 2. Initials Generation
    $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName ?: $firstName, ($lastName ? 0 : 1), 1));
    
    // 3. Styling Classes
    $sizeClass = "w-{$size} h-{$size}";
    $borderClass = $border > 0 ? "border-{$border}" : "border-none";
    $roundedClass = $circular ? 'rounded-full' : 'rounded-[35px]';

    // 4. Profile Picture URL Resolution
    $profileUrl = null;
    if ($profilePic) {
        // Handle paths that might or might not have 'storage/' prefix
        if (str_starts_with($profilePic, 'profiles/')) {
            $profileUrl = asset('storage/' . $profilePic);
        } else {
            $profileUrl = asset($profilePic);
        }
    }
@endphp

<div class="shrink-0 flex items-center justify-center {{ $sizeClass }} {{ $roundedClass }} transition-colors duration-300">
    @if($profileUrl)
        <img src="{{ $profileUrl }}" 
             alt="Profile" 
             class="{{ $sizeClass }} {{ $roundedClass }} object-cover {{ $borderClass }} border-white/20 dark:border-gray-700 shadow-sm">
    @else
        <div class="{{ $sizeClass }} {{ $roundedClass }} {{ $background }} flex items-center justify-center {{ $borderClass }} border-white/20 dark:border-gray-600 overflow-hidden text-{{ $textSize }} font-bold text-white">
            {{ $initials }}
        </div>
    @endif
</div>
