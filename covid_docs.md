FORMAT: 1A

# Covid

# Covid [/covid]
Covid Controller resource representation.

+ Parameters
    + id: (integer, required) - The id of the sample.

## Register a resource. [POST /covid]


+ Request (application/json)
    + Body

            {
                "case_id": "int, case number",
                "identifier_type": "int, identifier type",
                "identifier": "string, actual identifier, National ID... ",
                "patient_name": "string",
                "justification": "int, reason for the test",
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
                "lab_id": "int",
                "test_type_id": "int",
                "occupation": "string",
                "temperature": "int, temp in Celcius",
                "sample_type": "int, refer to ref tables",
                "symptoms": "array of integers, refer to ref tables",
                "observed_signs": "array of integers, refer to ref tables",
                "underlying_conditions": "array of integers, refer to ref tables"
            }

+ Response 201 (application/json)