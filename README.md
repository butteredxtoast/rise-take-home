# Rise Take Home Assignment
## This was a blast! Here's a rough breakdown of the steps I took to complete the assignment:

###  Thought Process:
- first minute was spent deciding what music to put on
- With the freedom to choose whatever tech I want, I opted for a Laravel application
    - I considered using Python, but this assignment is time-bound and I wanted to use a framework I was already familiar with
    - I opted for an extremely paired down Laravel app with no front end configuration
    - To manage this app, I'm using Laravel Sail locally, which is a Docker-based development environment
- On project setup, I established a basic `/health` endpoint to ensure the app was running
    - additionally, I created a collection in Postman to test the API
- My BigQuery configuration is also very barebones. It's helpful that I don't need to modify or do anything special with this data other than retrieve it
    - I spent a few minutes reading the documentation for the BigQuery API to ensure I was using it correctly
- Hit a snag with CLoud Run configuration and ended up consulting AI (limited online documentation for Laravel apps)
    - Revealed that I needed to create a Procfile to run the app in Cloud Run. Basically CLoud Run needs to know to run `php artisan serve --host=0.0.0.0 --port=$PORT` to start my app
- Another snag! My normal project setup flow caused things to be more complicated than they needed to be! Working in a containerized environment, I overlooked needing to mount auth credentials into the container and setting an env var.
- Spent longer on the Cloud Run configuration than I expected, but it was a good learning experience
- - Now that my application is running in the cloud, I was able to test the `/health` endpoint and confirm that it was working

- Rockin & rollin' on API design now. Went with the iterative approach of building a simple request to return a single row via a basic `Select *` query
- Confirming that I can query BigQuery felt like a major milestone - I can really start developing now
- Planning to focus on happy path first, but did introduce a simple request validator
- first time I've worked with a non-standard column name ("Term Start")
- Fun "gotcha" was realizing that all of the columns that represent dates in the data are actually strings, so I had to convert them to Date (Carbon) objects
- One of Grover Cleveland's terms was input incorrectly // that set me back a few minutes getting that corrected
- Once I fixed the data issue and had my time conversion taken care of, I was able to get the first endpoint working
- Controller was looking a little bloated, so I extracted the query logic into a service class
- Now the _fun_ part - something random! This was a neat challenge in that I didn't want to import any external libraries, so I had to get creative with the data available
- Went with some most common/least common logic
- Little more abstraction after that endpoint was set

## A Note on AI:
- After getting stuck on Cloud Run configuration & BigQuery auth, I consulted AI with some specific questions to help me get unstuck
- I left co-pilot auto-complete on for the duration of the project to assist with some syntax as needed

## Testing:
I didn't get a chance to write any local tests (the scope of this app is also very basic)

Date Endpoint:
- Postman: `https://rise-take-home-1068340888922.us-west1.run.app/api/presidents/{mm-dd-yyyy}`
- cURL: `curl https://rise-take-home-1068340888922.us-west1.run.app/api/presidents/05-28-1991`

Fun Endpoint:
- Postman: `https://rise-take-home-1068340888922.us-west1.run.app/api/random`
- cURL: `curl -v https://rise-take-home-1068340888922.us-west1.run.app/api/random`
