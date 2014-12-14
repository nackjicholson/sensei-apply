# Sensei Application API
Apply by API to work at Cascade Energy.

# Applications [/api/v1/apply]

## Send Application [POST]
Send an application.

+ Headers
    
    Accept: application/json

+ Request

```json
{
    "profile": {"name": "Will Vaughn"},
    "resume": "file content"
}
```

+ Response 200 (text/plain)

```json
"Thank you for applying via api *profile.name*"
```
