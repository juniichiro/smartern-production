<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolCoordinatorDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'profile_picture',
        'school',
        'phone_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'school_coordinator_detail_id');
    }

    public static function getCoordinatorsForSchool($schoolName)
    {
        if (empty($schoolName)) {
            return collect();
        }

        // 1. Try exact match first
        $coords = self::where('school', $schoolName)->get();
        if ($coords->isNotEmpty()) {
            return $coords;
        }

        // 2. Map abbreviations to full names and vice-versa
        $map = [
            'UP' => ['University of the Philippines', 'University of the Philippines Diliman', 'University of the Philippines Los Baños', 'University of the Philippines Manila'],
            'UST' => ['University of Santo Tomas'],
            'DLSU' => ['De La Salle University', 'De La Salle - College of Saint Benilde'],
            'FEU-TECH' => ['FEU Institute of Technology', 'Far Eastern University'],
        ];

        // Find matches in map
        $matchedAbbrs = [];
        foreach ($map as $abbr => $fullNames) {
            // Check if $schoolName is the abbreviation
            if (strcasecmp($schoolName, $abbr) === 0) {
                $matchedAbbrs[] = $abbr;
            }
            // Check if $schoolName is in the full names
            foreach ($fullNames as $fullName) {
                if (strcasecmp($schoolName, $fullName) === 0) {
                    $matchedAbbrs[] = $abbr;
                }
            }
        }

        if (!empty($matchedAbbrs)) {
            return self::whereIn('school', $matchedAbbrs)->get();
        }

        // 3. Fallback: loose normalized substring matching (e.g., remove spaces and hyphens)
        $normalizedSchoolName = str_replace([' ', '-'], '', strtolower($schoolName));
        return self::where(function ($q) use ($normalizedSchoolName) {
            $q->whereRaw('LOWER(REPLACE(REPLACE(school, " ", ""), "-", "")) LIKE ?', ["%{$normalizedSchoolName}%"])
              ->orWhereRaw('? LIKE LOWER(CONCAT("%", REPLACE(REPLACE(school, " ", ""), "-", ""), "%"))', [$normalizedSchoolName]);
        })->get();
    }
}
