CREATE OR REPLACE VIEW old_samples_view AS
(
  SELECT s.*, b.high_priority, b.datereceived, b.datedispatched, b.site_entry, b.lab_id, f.facilitycode, 
  p.patient, p.sex, p.dob, p.mother_id, p.entry_point

  FROM samples s
  JOIN batches b ON b.id=s.batch_id
  LEFT JOIN facilitys f ON f.id=b.facility_id
  JOIN patients p ON p.id=s.patient_id

    SELECT s.id, s.batchno as batch_id, s.patientid as patient_id, 
    s.AMRSlocation as amrs_location, s.provideridentifier as provider_identifier, s.orderno as order_no,
    s.vlrequestno as vl_test_request_no, s.receivedstatus, p.age, s.age2 as age_category, s.justification,
    s.otherjustification as other_justification, s.sampletype, s.prophylaxis, s.regimenline, s.pmtcttype as pmtct,
    s.dilutionfactor, s.dilutiontype, s.comments, s.labcomment, s.parentid, s.rejectedreason, s.reason_for_repeat,
    s.rcategory, s.result, s.units, s.interpretation, s.worksheet_id, s.flag, s.run, s.repeatt, s.eqa, s.approvedby,
    s.approvedby2, s.datecollected, s.datetested, s.datemodified, s.dateapproved, s.dateapproved2, s.tat1,
    s.tat2, s.tat3, s.tat4, s.synched, s.datesynched, 

    s.highpriority as high_priority, s.inputcomplete, s.batchcomplete as batch_complete, s.siteentry as
    site_entry, s.sentemail as sent_email, s.printedby, s.userid as user_id, s.receivedby as received_by,
    s.labtestedin as lab_id, s.facility as facility_id, s.datedispatchedfromfacility, s.datereceived, s.datebatchprinted,
    s.datedispatched, s.dateindividualresultprinted, 

    s.patient, s.fullnames as patient_name, s.caregiverphoneno as s.caregiver_phone, p.gender,
    p.initiationdate as initiation_date

    FROM samples s
    JOIN patients p ON p.id=s.patientid

); 

CREATE OR REPLACE VIEW old_viralsamples_view AS
(
    SELECT s.id, s.batchno as batch_id, s.patientid as patient_id, 
    s.AMRSlocation as amrs_location, s.provideridentifier as provider_identifier, s.orderno as order_no,
    s.vlrequestno as vl_test_request_no, s.receivedstatus, p.age, s.age2 as age_category, s.justification,
    s.otherjustification as other_justification, s.sampletype, s.prophylaxis, s.regimenline, s.pmtcttype as pmtct,
    s.dilutionfactor, s.dilutiontype, s.comments, s.labcomment, s.parentid, s.rejectedreason, s.reason_for_repeat,
    s.rcategory, s.result, s.units, s.interpretation, s.worksheet_id, s.flag, s.run, s.repeatt, s.eqa, s.approvedby,
    s.approvedby2, s.datecollected, s.datetested, s.datemodified, s.dateapproved, s.dateapproved2, s.tat1,
    s.tat2, s.tat3, s.tat4, s.synched, s.datesynched, 

    s.highpriority as high_priority, s.inputcomplete, s.batchcomplete as batch_complete, s.siteentry as
    site_entry, s.sentemail as sent_email, s.printedby, s.userid as user_id, s.receivedby as received_by,
    s.labtestedin as lab_id, s.facility as facility_id, s.datedispatchedfromfacility, s.datereceived, s.datebatchprinted,
    s.datedispatched, s.dateindividualresultprinted, 

    s.patient, s.fullnames as patient_name, s.caregiverphoneno as s.caregiver_phone, p.gender,
    p.initiationdate as initiation_date

    FROM viralsamples s
    JOIN viralpatients p ON p.id=s.patientid

);
