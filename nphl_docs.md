FORMAT: 1A

# Covid


## Post complete results. [POST /covid/nhrl]


+ Request (application/json)
    + Headers

            apikey: secret key
    + Body

            {
                "case_id": "int, case number",
                "identifier_type": "int, identifier type",
                "identifier": "string, actual identifier, National ID... ",
                "patient_name": "string",
                "justification": "int, reason for the test, refer to ref tables",
                "facility": "string, MFL Code or DHIS Code of the facility if any",
                "county": "string",
                "subcounty": "string",
                "ward": "string",
                "residence": "string",
                "sex": "string, M for male, F for female",
                "health_status": "int, health status",
                "date_symptoms": "date",
                "date_admission": "date",
                "date_isolation": "date",
                "date_death": "date",
                "lab_id": "int, refer to ref tables, 7 NHRL, 11 NIC",
                "test_type": "int, refer to ref tables",
                "occupation": "string",
                "temperature": "int, temp in Celcius",
                "sample_type": "int, refer to ref tables",
                "symptoms": "array of integers, refer to ref tables",
                "observed_signs": "array of integers, refer to ref tables",
                "underlying_conditions": "array of integers, refer to ref tables",
                "datecollected": "date",
                "datereceived": "date",
                "datetested": "date",
                "datedispatched": "date",
                "receivedstatus": "int, refer to ref tables",
                "result": "int, refer to ref tables"
            }

+ Response 201 (application/json)