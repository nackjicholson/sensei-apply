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

## Run this app local

`npm install -g babel`
`git clone git@github.com:CascadeEnergy/sensei-apply.git`
`cd sensei-apply`
`npm install`
`cp config/default.yml config/local.yml`

Put in your slack token and channel id, to the `local.yml`

`babel-node es6/server.js`

Go to http://localhost:9000/docs

## Deploy this API to your AWS account and your slack channel.

You need node and npm. This project uses ES6, so you'll need to install babel. If you don't run nvm, you should.
You have to be on a box where you have full admin credentials, including ability to make IAM roles.

This set of instructions will spin up all of the AWS infrastructure for this app. You will have to pay for that.

`npm install -g babel`
`git clone git@github.com:CascadeEnergy/sensei-apply.git`
`cd sensei-apply`
`npm install`
`babel-node -- bin/sa` Help menu for the cli.
`babel-node -- bin/sa up --keyname "YOUR_PEM_KEYNAME" --security-groups "MY_SSH_SECURITY_GROUPNAME"`

SSH into the box which was created.
`cd /var/cascade/sensei-apply`
`cp config/default.yml config/local.yml`

Put your slack api token, and the ID of your slack channel in `local.yml`.

`cd support/`
`./ec2_setup.sh`

That should've installed nvm, iojs, pm2, and started 2 cluster processes running the server.

To check the logs of the running processes.

`. ~/.bash_profile`
`nvm use iojs`
`pm2 logs server`

You should be seeing your ELB performing "/health" calls.
You can access the api and docs via your ELB's Public DNS Name.
