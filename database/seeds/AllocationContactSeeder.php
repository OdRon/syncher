<?php

use Illuminate\Database\Seeder;

class AllocationContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$contact_details = [['lab_id' => 0, 'address' => 'Commercial Street, Industrial Area,P.O. Box 47715 - GPO 00100 Nairobi. Kenya.', 'contact_person' => 'Joshua Obell', 'telephone' => '254(20) 3922000'],
			['lab_id' => 1, 'address' => 'Centre of Virus Research, KEMRI Headquarters, Mbagathi way, Nairobi', 'contact_person' => 'Priska Bwana', 'telephone' => '0720105528', 'contact_person_2' => 'Dr. Matilu Mwau', 'telephone_2' => '0728073633'],
			['lab_id' => 2, 'address' => 'KEMRI Complex, HIV R Lab, KEMRI/ CDC Complex, Kisian, Kisumu-Busia Road, Kisumu', 'contact_person' => 'Erick Auma', 'telephone' => '0726639538', 'contact_person_2' => 'Emily Anyango', 'telephone_2' => '0733963168'],
			['lab_id' => 3, 'address' => 'Centre for Infectious and Parasitic Disease Reasearch, off Busia Highway, Busia', 'contact_person' => 'Katherine Syeunda', 'telephone' => '0726445474', 'contact_person_2' => 'Dr. Matilu Mwau', 'telephone_2' => '0728073633'],
			['lab_id' => 4, 'address' => 'Walter Reed Project Kericho, Opposite Kericho District Hospital, Kericho', 'contact_person' => 'Alex Kasambeli', 'telephone' => '0700310324', 'contact_person_2' => 'Loice Cheruiyot', 'telephone_2' => '0717737501'],
			['lab_id' => 5, 'address' => ' Moi Teaching and Referral Hospital Grounds, Nandi Road; Off uganda/ Nairobi Road; Eldoret',	'contact_person' => 'Dr. Sylvester Kimaiyo', 'telephone' => '0721781605', 'contact_person_2' => 'Silvia Kadima', 'telephone_2' => '0720824338'],
			['lab_id' => 6, 'address' => 'Molecular Section, Laboratoy, Hospital Road, Mombasa', 'contact_person' => 'Mr. Denje', 'telephone' => '0721772657', 'contact_person_2' => 'Raphael Dume', 'telephone_2' => '0720594408'],
			['lab_id' => 7, 'address' => 'NASCOP, KNH Grounds', 'contact_person' => 'Jospeh Ombayo', 'telephone' => '0721547885', 'contact_person_2' => 'Nancy Bowen', 'telephone_2' => '0722845874'],
			['lab_id' => 8, 'address' => 'Main Laboratory, Dagoretti Road, Karen', 'contact_person' => 'Sister Ann', 'telephone' => '0722539294'],
			['lab_id' => 9, 'address' => 'CCC PROGRAM, KNH GROUNDS,', 'contact_person' => 	'Alex Morwabe', 'telephone' => '0722539265', 'contact_person_2' => 'Dr Kibet Shikuku', 'telephone_2' => '0720789843'],
			['lab_id' => 10, 'address' => 'Main Laboratory, Donholm NAIROBI', 'contact_person' => 'David Njoroge', 'telephone' => '0720824582', 'contact_person_2' => 'Wilson Ndungu', 'telephone_2' => '0731751281']];

			foreach ($contact_details as $key => $contact_detail) {
				\App\AllocationContact::create($contact_detail);
			}
    }
}