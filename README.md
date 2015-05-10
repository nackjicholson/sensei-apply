# Sensei Application API
Apply by API to work at Cascade Energy.  
http://jobs.energysensei.info

# Applications [/apply]

## Send Application [POST]
Send an application.

+ Headers
    
    Accept: application/json  
    Content-Type: multipart/form-data

+ Request

    `name`: String e.g. "Bill Murray",
    `blurb`: String e.g. "Say whatever you want here",  
    `resume`: multipart/form-data upload of a PDF file.

+ Response 200 (text/plain)

```json
{
    "message": "Thank you for applying via api *profile[name]*"
}
```
