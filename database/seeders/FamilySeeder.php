<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;
use App\Models\UnionModel;
use App\Models\ParentChildEdge;
use App\Models\Event;
use Carbon\Carbon;

class FamilySeeder extends Seeder
{
    public function run(): void
    {
        // Grandparents
        $gpa = Person::create([
            'given_name' => 'राम',
            'family_name' => 'शर्मा',
            'display_name' => 'राम शर्मा',
            'gender' => 'male',
            'birth_date' => '1950-03-10',
            'bio' => 'गाउँका पुराना शिक्षक।'
        ]);
        $gma = Person::create([
            'given_name' => 'सीता',
            'family_name' => 'शर्मा',
            'display_name' => 'सीता शर्मा',
            'gender' => 'female',
            'birth_date' => '1953-07-20',
            'bio' => 'गृहिणी, समाजसेवी।'
        ]);

        $union1 = UnionModel::create([
            'spouse1_id' => $gpa->id,
            'spouse2_id' => $gma->id,
            'type' => 'marriage',
            'start_date' => '1970-05-01'
        ]);

        // Their son + his spouse
        $dad = Person::create([
            'given_name' => 'बिवश',
            'family_name' => 'शर्मा',
            'display_name' => 'बिवश शर्मा',
            'gender' => 'male',
            'birth_date' => '1975-11-02',
            'bio' => 'ईन्जिनियर।'
        ]);
        $mom = Person::create([
            'given_name' => 'सुजाता',
            'family_name' => 'शर्मा',
            'display_name' => 'सुजाता शर्मा',
            'gender' => 'female',
            'birth_date' => '1978-01-18',
            'bio' => 'शिक्षिका।'
        ]);

        $union2 = UnionModel::create([
            'spouse1_id' => $dad->id,
            'spouse2_id' => $mom->id,
            'type' => 'marriage',
            'start_date' => '2000-02-14'
        ]);

        // Edges: grandparents -> dad
        ParentChildEdge::create(['parent_id' => $gpa->id, 'child_id' => $dad->id, 'relation_type' => 'birth']);
        ParentChildEdge::create(['parent_id' => $gma->id, 'child_id' => $dad->id, 'relation_type' => 'birth']);

        // Children
        $kid1 = Person::create([
            'given_name' => 'राकेश',
            'family_name' => 'शर्मा',
            'display_name' => 'राकेश शर्मा',
            'gender' => 'male',
            'birth_date' => '2002-06-05',
            'bio' => 'कम्प्युटर विज्ञान विद्यार्थी।'
        ]);
        $kid2 = Person::create([
            'given_name' => 'रीना',
            'family_name' => 'शर्मा',
            'display_name' => 'रीना शर्मा',
            'gender' => 'female',
            'birth_date' => '2006-09-12',
            'bio' => 'संगीत मनपर्छ।'
        ]);
        // Kid1 marriage + kids
        $k1spouse = Person::create([
            'given_name' => 'अनुष्का',
            'family_name' => 'वराइली',
            'display_name' => 'अनुष्का वराइली',
            'gender' => 'female',
            'birth_date' => '2003-01-20',
            'bio' => 'डिजाइन रुचि।'
        ]);
        $k1union = UnionModel::create([
            'spouse1_id' => $kid1->id,
            'spouse2_id' => $k1spouse->id,
            'type' => 'marriage',
            'start_date' => '2026-04-12'
        ]);
        $k1c1 = Person::create([
            'given_name' => 'आयुष',
            'family_name' => 'शर्मा',
            'display_name' => 'आयुष शर्मा',
            'gender' => 'male',
            'birth_date' => '2028-02-10'
        ]);
        $k1c2 = Person::create([
            'given_name' => 'अन्वी',
            'family_name' => 'शर्मा',
            'display_name' => 'अन्वी शर्मा',
            'gender' => 'female',
            'birth_date' => '2031-09-01'
        ]);
        ParentChildEdge::create(['parent_id' => $kid1->id,   'child_id' => $k1c1->id, 'relation_type' => 'birth']);
        ParentChildEdge::create(['parent_id' => $k1spouse->id, 'child_id' => $k1c1->id, 'relation_type' => 'birth']);
        ParentChildEdge::create(['parent_id' => $kid1->id,   'child_id' => $k1c2->id, 'relation_type' => 'birth']);
        ParentChildEdge::create(['parent_id' => $k1spouse->id, 'child_id' => $k1c2->id, 'relation_type' => 'birth']);

        // Kid2 marriage + kids
        $k2spouse = Person::create([
            'given_name' => 'सौरभ',
            'family_name' => 'ढकाल',
            'display_name' => 'सौरभ ढकाल',
            'gender' => 'male',
            'birth_date' => '2004-03-14',
            'bio' => 'संगीत कलाकार।'
        ]);
        $k2union = UnionModel::create([
            'spouse1_id' => $k2spouse->id,
            'spouse2_id' => $kid2->id,
            'type' => 'marriage',
            'start_date' => '2030-11-20'
        ]);
        $k2c1 = Person::create([
            'given_name' => 'ईशा',
            'family_name' => 'ढकाल',
            'display_name' => 'ईशा ढकाल',
            'gender' => 'female',
            'birth_date' => '2033-06-25'
        ]);
        ParentChildEdge::create(['parent_id' => $k2spouse->id, 'child_id' => $k2c1->id, 'relation_type' => 'birth']);
        ParentChildEdge::create(['parent_id' => $kid2->id,   'child_id' => $k2c1->id, 'relation_type' => 'birth']);


        ParentChildEdge::create(['parent_id' => $dad->id, 'child_id' => $kid1->id, 'relation_type' => 'birth']);
        ParentChildEdge::create(['parent_id' => $mom->id, 'child_id' => $kid1->id, 'relation_type' => 'birth']);
        ParentChildEdge::create(['parent_id' => $dad->id, 'child_id' => $kid2->id, 'relation_type' => 'birth']);
        ParentChildEdge::create(['parent_id' => $mom->id, 'child_id' => $kid2->id, 'relation_type' => 'birth']);

        // Events examples
        Event::create([
            'person_id' => $kid1->id,
            'title' => 'कम्प्युटर विज्ञान स्नातक अध्ययन',
            'event_type' => 'education',
            'event_date' => '2021-08-15',
            'description' => 'काठमाडौंमा अध्ययन सुरु।'
        ]);
        Event::create([
            'person_id' => $dad->id,
            'title' => 'प्रमोशन',
            'event_type' => 'job',
            'event_date' => '2010-04-01',
            'description' => 'वरिष्ठ ईन्जिनियर।'
        ]);

        // A deceased example
        $uncle = Person::create([
            'given_name' => 'मोहन',
            'family_name' => 'शर्मा',
            'display_name' => 'मोहन शर्मा',
            'gender' => 'male',
            'birth_date' => '1980-05-10',
            'death_date' => '2018-12-01',
            'is_deceased' => true,
            'bio' => 'कविता प्रेमी।'
        ]);
        ParentChildEdge::create(['parent_id' => $gpa->id, 'child_id' => $uncle->id, 'relation_type' => 'birth']);
        ParentChildEdge::create(['parent_id' => $gma->id, 'child_id' => $uncle->id, 'relation_type' => 'birth']);
    }
}
