<?php

use Illuminate\Http\UploadedFile;

test('it successfully parses a valid pdf resume template', function () {
    $filePath = base_path('FEU TECH Student Résumé ver. 2024-2025.pdf');
    
    // Create an UploadedFile instance from the actual file
    $uploadedFile = new UploadedFile(
        $filePath,
        'FEU_TECH_Student_Resume.pdf',
        'application/pdf',
        null,
        true // test mode
    );

    $response = $this->post(route('onboarding.parse'), [
        'resume_pdf' => $uploadedFile,
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'first_name',
        'last_name',
        'university',
        'email',
        'phone_number',
    ]);

    // Check specific fields parsed from the template
    $data = $response->json();
    expect($data['first_name'])->toBe('Carlo A.');
    expect($data['last_name'])->toBe('Briones');
    expect($data['university'])->toBe('Feu Institute Of Technology');
    expect($data['email'])->toBe('cbriones@emaildomain.com');
    expect($data['phone_number'])->toBe('09303044030');
});

test('it fails validation when no file is uploaded', function () {
    $response = $this->post(route('onboarding.parse'), [], [
        'HTTP_ACCEPT' => 'application/json'
    ]);

    $response->assertStatus(422);
});

test('it fails parsing when an invalid or empty pdf is uploaded', function () {
    // Generate a dummy text file with PDF extension
    $uploadedFile = UploadedFile::fake()->create('invalid.pdf', 10, 'application/pdf');

    $response = $this->post(route('onboarding.parse'), [
        'resume_pdf' => $uploadedFile,
    ]);

    $response->assertStatus(422);
});
