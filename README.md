# Quicksilver
Documentation for ontavio mail service connector

----------------
**TODO** and possible Features:

- 

----------------
## Set up connector
1. create JWT token for project
   - use loginAsUser() function or get JWT manually


2. create new Connector() with
   - $endpoint -> your database url
   - $auth -> your JWT token used to verify user
   - additional [parameters](#constructor-parameters) can be specified


3. use the functions under [functions](#functions) for CRUD actions
    - for edgecases use executeQuery() function to use your own queries

## Create connector object

### Constructor parameters

- $endpoint
  - set endpoint to connect to as string.


- $auth
  - insert JWT used to connect to endpoint.


- $unSpamSubject
  - enable "subject" field formatting to make Email less likely to be marked as spam.


- $convertHtmlToText
  - fill "text" field with content from "html" field.

### Functions

**NOTE:** if a function finished successfully it returns json, on failure it returns null

- setEndpoint(string $newEndpoint)
  - override endpoint string


- setAuth(string $newAuth)
  - override JWT authorization string


- loginAsUser(string $endpoint, string $user, string $password)
  - returns JWT token when giving valid endpoint, user and password 
  - *ONLY use this to create initial JWT and save it*


- executeQuery(string $endpoint, string $query, string $auth)
  - submits query to endpoint using auth
  - returns query result


- removeAllHtml($text, array $replaceProjectSpecific)
  - removes all html tags and additional strings in array


- create(Email $email)
  - sends email to specified endpoint
  - returns the created email id

- read(string $emailEId)
  - returns email based on $emailEId


- readStatus(string $emailEId)
  - returns "sent", "rejected" and "status" fields


- getEmailId(string $emailEId)
  - returns emailId by searching email*E*Id
  - mainly used for specific queries


- update(Email $email)
  - updates whole email provided
  - eId identifies email to update
  - returns eId of updated mail on success


- delete(string $emailEId)
  - deletes email based on eId
  - returns eId and id


### Email class
Email class contains getters and setters for all parameters
```
        $attachments -> use Attachment Class
bool    $attachDataUrls
array   $bcc
array   $cc
        $delivery //Datetimefomat in DB -> "Y-m-d\TH:i:s.v\Z"
string  $eId
string  $html
string  $htmlTemplate
string  $messageId
        $priority // enum LOW/NORMAL/HIGH
string  $project
string  $replyTo
string  $sender
bool    $single
        $stack 
string  $subject
string  $templateData
string  $text
string  $textTemplate
array   $to
``` 