FORMAT: 1A

# Covid

# Login

## Login as a user. [POST /auth/login]
Middleware Guest

Use the token to authorise your other requests

Pass the token in a header

Authorization: bearer {token}

+ Request (application/json)
    + Body

            {
                "email": "email",
                "password": "string"
            }

+ Response 200 (application/json)
    + Body

            {
                "status": "ok",
                "token": "token",
                "expires_in": "ttl in minutes"
            }

# Covid [/covid]
Covid Controller resource representation.

+ Parameters
    + id: (integer, required) - The id of the sample.

## Display a listing of the resource. [GET /covid{?page}]
The response has links to navigate to the rest of the data.

+ Response 200 (application/json)
    + Body

            {
                "data": {
                    "sample": {
                        "id": "int",
                        "patient": {
                            "id": "int"
                        }
                    }
                }
            }

## Register a resource. [POST /covid]


+ Request (application/json)
    + Body

            {
                "case_id": "int, case number",
                "identifier_type": "int, identifier type",
                "identifier": "string, actual identifier, National ID... ",
                "patient_name": "string",
                "justification": "int, reason for the test",
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
                "lab_id": "int, refer to ref tables",
                "test_type": "int",
                "occupation": "string",
                "temperature": "int, temp in Celcius",
                "sample_type": "int, refer to ref tables",
                "symptoms": "array of integers, refer to ref tables",
                "observed_signs": "array of integers, refer to ref tables",
                "underlying_conditions": "array of integers, refer to ref tables"
            }

+ Response 201 (application/json)

## Display the specified resource. [GET /covid/{id}]


+ Response 200 (application/json)
    + Body

            {
                "sample": {
                    "id": "int",
                    "patient": {
                        "id": "int"
                    }
                }
            }

## Register multiple resources. [POST /covid]


+ Request (application/json)
    + Body

            {
                "sample": [
                    {
                        "case_id": "int, case number",
                        "identifier_type": "int, identifier type",
                        "identifier": "string, actual identifier, National ID... ",
                        "patient_name": "string",
                        "justification": "int, reason for the test",
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
                        "lab_id": "int, refer to ref tables",
                        "test_type": "int",
                        "occupation": "string",
                        "temperature": "int, temp in Celcius",
                        "sample_type": "int, refer to ref tables",
                        "symptoms": "array of integers, refer to ref tables",
                        "observed_signs": "array of integers, refer to ref tables",
                        "underlying_conditions": "array of integers, refer to ref tables"
                    }
                ]
            }

+ Response 201 (application/json)