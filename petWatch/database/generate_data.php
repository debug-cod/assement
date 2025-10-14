<?php
// Script to generate 100 random pet records
// Run this once to populate the database

// Database connection
$db = new PDO('sqlite:petwatch.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Sample data arrays
$species = ['Cat', 'Dog', 'Bird', 'Rabbit', 'Hamster', 'Fish', 'Turtle', 'Snake', 'Lizard'];
$breeds = [
    'Cat' => ['Tabby', 'Siamese', 'Persian', 'Maine Coon', 'Bengal'],
    'Dog' => ['Retriever', 'Terrier', 'Bulldog', 'Poodle', 'Beagle'],
    'Bird' => ['Parrot', 'Canary', 'Finch', 'Cockatiel', 'Parakeet'],
    // ... other species breeds
];
$colors = ['White', 'Black', 'Red', 'Purple', 'Multi-color', 'Grey', 'Yellow', 'Brown', 'Orange'];
$genders = ['Male', 'Female'];

// Generate 100 pets
for ($i = 1; $i <= 100; $i++) {
    $species = $species[array_rand($species)];
    $breed = $breeds[$species][array_rand($breeds[$species])];

    $stmt = $db->prepare("INSERT INTO pets (name, species, breed, color, status, description, date_reported, user_id, gender, age) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        "Pet_" . $i,
        $species,
        $breed,
        $colors[array_rand($colors)],
        rand(0, 1) ? 'lost' : 'found',
        "Description for pet " . $i,
        date('Y-m-d', strtotime('-' . rand(0, 365) . ' days')),
        rand(1, 4),
        $genders[array_rand($genders)],
        rand(1, 2000) / 100 // Age between 0.01 and 20.00
    ]);
}

echo "100 pets generated successfully!";
?>
