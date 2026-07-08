<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicines = [
            [
                'name' => 'Panadol',
                'generic_name' => 'Paracetamol',
                'brand' => 'GSK',
                'category' => 'Tablet',
                'strength' => '500mg',
                'content' => 'Panadol is used for fever, headache, body aches, toothache, and mild pain. Adults should take 1 tablet every 4 to 6 hours as needed. Do not exceed 8 tablets in 24 hours. Common side effects are rare but may include nausea or skin rash.',
                'prescription_required' => false,
            ],
            [
                'name' => 'Brufen',
                'generic_name' => 'Ibuprofen',
                'brand' => 'Abbott',
                'category' => 'Tablet',
                'strength' => '400mg',
                'content' => 'Brufen is used to relieve pain, inflammation, fever, arthritis, and menstrual cramps. Take after meals. Avoid in patients with stomach ulcers or severe kidney disease.',
                'prescription_required' => false,
            ],
            [
                'name' => 'Augmentin',
                'generic_name' => 'Amoxicillin + Clavulanic Acid',
                'brand' => 'GSK',
                'category' => 'Tablet',
                'strength' => '625mg',
                'content' => 'Augmentin is an antibiotic used for bacterial infections including respiratory, skin, and urinary tract infections. Complete the prescribed course. May cause diarrhea or nausea.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Flagyl',
                'generic_name' => 'Metronidazole',
                'brand' => 'Sanofi',
                'category' => 'Tablet',
                'strength' => '400mg',
                'content' => 'Flagyl treats bacterial and parasitic infections. Avoid alcohol during treatment and for at least 48 hours after the last dose.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Ponstan',
                'generic_name' => 'Mefenamic Acid',
                'brand' => 'Pfizer',
                'category' => 'Tablet',
                'strength' => '500mg',
                'content' => 'Ponstan is used for pain relief including menstrual pain and dental pain. Take after meals to reduce stomach irritation.',
                'prescription_required' => false,
            ],
            [
                'name' => 'Glucophage',
                'generic_name' => 'Metformin',
                'brand' => 'Merck',
                'category' => 'Tablet',
                'strength' => '500mg',
                'content' => 'Glucophage helps control blood sugar in type 2 diabetes. Take with food to reduce stomach upset.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Amoxil',
                'generic_name' => 'Amoxicillin',
                'brand' => 'GSK',
                'category' => 'Capsule',
                'strength' => '500mg',
                'content' => 'Amoxil is a penicillin antibiotic used for bacterial infections. Finish the full prescribed course even if symptoms improve.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Ventolin',
                'generic_name' => 'Salbutamol',
                'brand' => 'GSK',
                'category' => 'Inhaler',
                'strength' => '100mcg',
                'content' => 'Ventolin provides quick relief from asthma symptoms and bronchospasm. Overuse may cause rapid heartbeat and tremors.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Nexium',
                'generic_name' => 'Esomeprazole',
                'brand' => 'AstraZeneca',
                'category' => 'Capsule',
                'strength' => '40mg',
                'content' => 'Nexium reduces stomach acid and treats GERD, ulcers, and acid reflux. Take before meals.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Zyrtec',
                'generic_name' => 'Cetirizine',
                'brand' => 'UCB',
                'category' => 'Tablet',
                'strength' => '10mg',
                'content' => 'Zyrtec relieves allergy symptoms such as sneezing, runny nose, and itching. It may cause mild drowsiness.',
                'prescription_required' => false,
            ],
            [
                'name' => 'Claritin',
                'generic_name' => 'Loratadine',
                'brand' => 'Bayer',
                'category' => 'Tablet',
                'strength' => '10mg',
                'content' => 'Claritin is a non-drowsy antihistamine used for seasonal allergies and hives.',
                'prescription_required' => false,
            ],
            [
                'name' => 'Lipitor',
                'generic_name' => 'Atorvastatin',
                'brand' => 'Pfizer',
                'category' => 'Tablet',
                'strength' => '20mg',
                'content' => 'Lipitor lowers cholesterol and reduces the risk of heart disease. Regular liver function monitoring may be required.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Norvasc',
                'generic_name' => 'Amlodipine',
                'brand' => 'Pfizer',
                'category' => 'Tablet',
                'strength' => '5mg',
                'content' => 'Norvasc treats high blood pressure and angina. Common side effects include ankle swelling and dizziness.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Cozaar',
                'generic_name' => 'Losartan',
                'brand' => 'Merck',
                'category' => 'Tablet',
                'strength' => '50mg',
                'content' => 'Cozaar is used for hypertension and kidney protection in diabetic patients. Monitor blood pressure regularly.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Imodium',
                'generic_name' => 'Loperamide',
                'brand' => 'Janssen',
                'category' => 'Capsule',
                'strength' => '2mg',
                'content' => 'Imodium provides short-term relief from diarrhea. Maintain adequate hydration while using this medicine.',
                'prescription_required' => false,
            ],
            [
                'name' => 'Buscopan',
                'generic_name' => 'Hyoscine Butylbromide',
                'brand' => 'Sanofi',
                'category' => 'Tablet',
                'strength' => '10mg',
                'content' => 'Buscopan relieves stomach cramps, abdominal pain, and intestinal spasms.',
                'prescription_required' => false,
            ],
            [
                'name' => 'Diflucan',
                'generic_name' => 'Fluconazole',
                'brand' => 'Pfizer',
                'category' => 'Capsule',
                'strength' => '150mg',
                'content' => 'Diflucan is an antifungal medicine used to treat fungal infections including candidiasis.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Cipro',
                'generic_name' => 'Ciprofloxacin',
                'brand' => 'Bayer',
                'category' => 'Tablet',
                'strength' => '500mg',
                'content' => 'Cipro is a broad-spectrum antibiotic used for bacterial infections. Avoid taking with dairy products alone.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Voltaren',
                'generic_name' => 'Diclofenac',
                'brand' => 'Novartis',
                'category' => 'Tablet',
                'strength' => '50mg',
                'content' => 'Voltaren relieves pain and inflammation in arthritis, injuries, and muscle pain. Take after meals.',
                'prescription_required' => true,
            ],
            [
                'name' => 'Aspirin',
                'generic_name' => 'Acetylsalicylic Acid',
                'brand' => 'Bayer',
                'category' => 'Tablet',
                'strength' => '75mg',
                'content' => 'Low-dose aspirin helps prevent blood clots and reduces the risk of heart attack and stroke. Not recommended for children with viral infections.',
                'prescription_required' => true,
            ],
        ];

        foreach ($medicines as $medicine) {
            Medicine::updateOrCreate(
                ['name' => $medicine['name']],
                $medicine
            );
        }
    }
}
