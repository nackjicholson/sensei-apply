# Sensei Resumes API
Apply by API to work at Cascade Energy.

If you're interested in working with us, put yourself at the top of our list
by applying via API. If you write some code to do it, perhaps you can show us through
it when we get in touch. Also, we'd love feedback on the API, good or bad.

We are a small DevOps team based in Portland, OR working with a fascinating data set, and
exciting new technologies. If you're interested in how we do things, go ahead and look through the code in
this repo, and our open source projects at http://github.com/CascadeEnergy

## Check the Live API's Generated Documentation
#### http://jobs.energysensei.info/docs

# Resumes [/resumes]
## Send Resume [POST]
Send a Resume.

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
    "message": "Thank you, we received your resume."
}
```
