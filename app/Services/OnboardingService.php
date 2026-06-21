<?php

namespace App\Services;

class OnboardingService
{
    /**
     * Process skills from the request into a structured array.
     */
    public function formatSkills(array $skills, array $scores): array
    {
        $formatted = [];
        foreach ($skills as $index => $skill) {
            if (! empty(trim($skill))) {
                $formatted[trim($skill)] = (int) ($scores[$index] ?? 3);
            }
        }

        return $formatted;
    }

    /**
     * Handle the file upload for the resume.
     */
    public function storeResume($file): string
    {
        return $file->store('resumes', 'public');
    }

    /**
     * Generate a unique applicant ID.
     */
    public function generateApplicantId(): string
    {
        return 'APP-'.rand(1000, 9999);
    }
}
