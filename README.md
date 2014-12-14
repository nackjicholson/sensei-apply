# Sensei Application API
Apply by API to work at Cascade Energy.  
https://apply.energysensei.info

# Applications [/api/v1/apply]

## Send Application [POST]
Send an application.

+ Headers
    
    Accept: application/json  
    Content-Type: multipart/form-data

+ Request

    `profile[name]`: String e.g. "Bill Murray"  
    `resume`: multipart/form-data upload of a PDF file.

+ Response 200 (text/plain)

```json
{
    "message": "Thank you for applying via api *profile[name]*"
}
```
