<?php

namespace Quicksilver;


use ApiPlatform\Core\GraphQl\Action\EntrypointAction;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use phpDocumentor\Reflection\TypeResolver;
use stringEncode\Exception;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

class Connector
{
    /**
     * @var string
     */
    private $endpoint;
    /**
     * @var string
     */
    private $auth;
    /**
     * @var bool
     */
    private $generateTextFromHTML;

    /**
     * @var array
     */
    private $replaceProjectSpecific;

    /**
     * @param string  $endpoint
     * @param string  $auth
     * @param bool    $generateTextFromHTML
     * @param bool    $convertHtmlToText
     */
    public function __construct(string $endpoint = "", string $auth = "", bool $generateTextFromHTML = true, array $replaceProjectSpecific = [])
    {
        $this->endpoint = $endpoint;
        $this->auth = $auth;
        $this->generateTextFromHTML = $generateTextFromHTML;
        $this->replaceProjectSpecific = $replaceProjectSpecific;
    }

    /**
     * @param string  $newEndpoint
     *
     * @return void
     */
    public function setEndpoint(string $newEndpoint)
    {
        $this->endpoint = $newEndpoint;
    }


    /**
     * @param string  $newAuth
     *
     * @return void
     */
    public function setAuth(string $newAuth)
    {
        $this->auth = $newAuth;
    }


    /**
     * log in as user and keep JWT active in connector object
     *
     * @param string  $endpoint
     * @param string  $user
     * @param string  $password
     *
     * @return void
     */
    public function loginAsUser(string $endpoint, string $user, string $password)
    {
        $this->endpoint = $endpoint;
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $body = '{"query":"query {\\n  signIn(input: { email: \\"' . $user . '\\", password: \\"' . $password . '\\" }) {\\n    token\\n  }\\n}\\n","variables":{}}';
        $request = new \GuzzleHttp\Psr7\Request('POST', $endpoint, $headers, $body);
        $res = $client->sendAsync($request)->wait();

        $this->setAuth(json_decode($res->getBody()->getContents())->data->signIn->token);
    }

    /**
     * Execute all passed queries.
     *
     * @param string  $endpoint
     * @param string  $query
     * @param string  $auth
     *
     * @return mixed
     */
    public function executeQuery(string $endpoint, string $query, string $auth)
    {

        //guzzle connection
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $auth,
        ];

        $request = new Request('POST', $endpoint, $headers, $query);
        $res = $client->sendAsync($request)->wait();

        return json_decode($res->getBody()->getContents());
    }

    /**
     * makes a html E-Mail to txt-Email
     *
     * @param        $text
     * @param array  $replaceProjectSpecific
     *
     * @return mixed|string|null
     */
    public function removeAllHtml($text, array $replaceProjectSpecific)
    {
        /*
         * what doesn't work yet: remove all a tags and leave only the blank links
         * but is this necessary?
         */
        $text = preg_replace("/\r|\n/", "", $text); // remove all line breaks
        $text = preg_replace("/\s\s+/", " ", $text); // remove all line breaks
        $text = preg_replace('/(<\/*br>|<\/*p>|<\/*ol>|<\/*ul>)/m', PHP_EOL, $text); // insert line breaks
        $text = str_replace('<li>', '-', $text); // dashes for list items
        $text = str_replace('</li>', '', $text); // remove for correct html
        $text = strip_tags($text); // remove all html tags, no exceptions
        $text = preg_replace('/(@import.*(\. --|}))/m', '', $text); // remove our css
        $text = preg_replace('/^ /m', '', $text); // if there are whitespaces at the beginning of a line, remove them

        //remove additional strings
        $text = preg_replace('/\b(' . implode('|', $replaceProjectSpecific) . ')( )\b/', '', $text);

        return $text;
    }


    /**
     * create email
     *
     * @param Email  $email
     *
     * @return json
     */
    public function create(Email $email)
    {

        //transform Datetime to DB format
        $transform = new DateTimeToStringTransformer(null, null, "Y-m-d\TH:i:s.v\Z");

        $text = "";
        if ($this->generateTextFromHTML == true && empty($email->getText())) {
            $text = $this->removeAllHtml($email->getHtml(), $this->replaceProjectSpecific);
        } else {
            $text = $email->getText();
        }

        if (empty($email->getAttachments())) {
            $query = '{"query": "mutation { createEmail( input: { ' .
                'bcc: [\\"' . implode('\\",\\"', $email->getBcc()) . '\\"] ' .
                'cc: [\\"' . implode('\\",\\"', $email->getCc()) . '\\"] ' .
                'delivery:\\"' . $transform->transform($email->getDelivery()) . '\\" ' .
                'eId: \\"' . $email->getEId() . '\\" ' .
                'html: \\"' . $email->getHtml() . '\\" ' .
                'htmlTemplate: \\"' . $email->getHtmlTemplate() . '\\" ' .
                'messageId: \\"' . $email->getMessageId() . '\\" ' .
                'priority: ' . $email->getPriority() . ' ' .
                'project: \\"' . $email->getProject() . '\\" ' .
                'replyTo: \\"' . $email->getReplyTo() . '\\" ' .
                'sender: \\"' . $email->getSender() . '\\" ' .
                'single: ' . json_encode($email->isSingle()) . ' ' .
                'stack: [] ' . //not implemented yet
                'subject: \\"' . $email->getSubject() . '\\" ' .
                'templateData: \\"' . $email->getTemplateData() . '\\" ' .
                'text: \\"' . $text . '\\" ' .
                'textTemplate: \\"' . $email->getTextTemplate() . '\\" ' .
                'to:[\\"' . implode('","', $email->getTo()) . '\\"] ' .
                '}){ eId }}"}';
        } else {
            $query = '{"query": "mutation ($attachments: [Upload!]){createEmail(input: {subject: \\"' . $email->getSubject() . '\\",messageId: \\"' . $email->getMessageId() . '\\",delivery:\\"' . $transform->transform($email->getDelivery()) . '\\",sender: \\"' . $email->getSender() . '\\",single: ' . json_encode($email->isSingle()) . ',priority: ' . $email->getPriority() . ',templateData: \\"' . $email->getTemplateData() . '\\",eId: \\"' . $email->getEId() . '\\",to: [\\"' . implode('","', $email->getTo()) . '\\"],cc: [\\"' . implode('\\",\\"', $email->getCc()) . '\\"],bcc: [\\"' . implode('\\",\\"', $email->getBcc()) . '\\"],text: \\"' . $text . '\\",textTemplate: \\"' . $email->getTextTemplate() . '\\",html: \\"' . $email->getHtml() . '\\",htmlTemplate: \\"' . $email->getHtmlTemplate() . '\\" ,project: \\"' . $email->getProject() . '\\",replyTo: \\"' . $email->getReplyTo() . '\\",attachDataUrls: ' . json_encode($email->isSingle()) . ',attachments: $attachments}) {id}}"}';
        }

        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJlbWFpbCI6ImFkbWluQG9udGF2aW8uZGUiLCJpYXQiOjE2NjMyMjU3ODV9.NwZlzKYaSBAt4xpJxt-6g0qmtDZBELzfvx-TgiFaHuw',
        ];
        $options = [
            'multipart' => [
                [
                    'name' => 'operations',
                    'contents' => $query,
                ],
            ]];

        //push file map depending on how many uploads are defined
        $map = '{';
        for ($i = 0; count($email->getAttachments()) > $i; $i++) {
            $map .= '"file' . $i . '":["variables.attachments.' . $i . '"]';
            //dont append , for last in array
            if ($i != count($email->getAttachments()) - 1) {
                $map .= ',';
            }
        }
        $map .= '}';

        array_push($options['multipart'], [
            'name' => 'map',
            'contents' => $map,
        ]);

        //push uploads as multipart form file
        $loop = 0;
        foreach ($email->getAttachments() as $file) {
            array_push($options['multipart'], [
                'name' => 'file' . $loop,
                'contents' => Psr7\try_fopen($file->getFilePath(), 'r'),
                'filename' => $file->getFileName(),
                'headers' => [
                    'Content-Type' => '<Content-type header>',
                ],
            ]);
            $loop++;
        };

        $request = new Request('POST', $this->endpoint, $headers);
        $res = $client->sendAsync($request, $options)->wait();

        return json_decode($res->getBody()->getContents());
    }

    /**
     * update existing email
     *
     * @param Email  $email
     *
     * @return json
     */
    public function update(Email $email)
    {
        $targetId = $this->getEmailId($email->getEId());

        $text = "";
        if ($this->generateTextFromHTML == true && empty($email->getText())) {
            $text = $this->removeAllHtml($email->getHtml(), $this->replaceProjectSpecific);
        } else {
            $text = $email->getText();
        }

        //transform Datetime to DB format
        $transform = new DateTimeToStringTransformer(null, null, "Y-m-d\TH:i:s.v\Z");

        if (empty($email->getAttachments())) {
            $query = '{"query": "mutation { updateEmail( id: \\"' . $targetId . '\\"input: { ' .
                'attachments: [] ' . //add attachment support
                'attachDataUrls: ' . json_encode($email->isSingle()) . ' ' . //add attachment support
                'bcc: [\\"' . implode('\\",\\"', $email->getBcc()) . '\\"] ' .
                'cc: [\\"' . implode('\\",\\"', $email->getCc()) . '\\"] ' .
                'delivery:\\"' . $transform->transform($email->getDelivery()) . '\\" ' .
                'eId: \\"' . $email->getEId() . '\\" ' .
                'html: \\"' . $email->getHtml() . '\\" ' .
                'htmlTemplate: \\"' . $email->getHtmlTemplate() . '\\" ' .
                'messageId: \\"' . $email->getMessageId() . '\\" ' .
                'priority: ' . $email->getPriority() . ' ' .
                'project: \\"' . $email->getProject() . '\\" ' .
                'replyTo: \\"' . $email->getReplyTo() . '\\" ' .
                'sender: \\"' . $email->getSender() . '\\" ' .
                'single: ' . json_encode($email->isSingle()) . ' ' .
                'stack: [] ' .
                'subject: \\"' . $email->getSubject() . '\\" ' .
                'templateData: \\"' . $email->getTemplateData() . '\\" ' .
                'text: \\"' . $text . '\\" ' .
                'textTemplate: \\"' . $email->getTextTemplate() . '\\" ' .
                'to:[\\"' . implode('","', $email->getTo()) . '\\"] ' .
                '}){ eId }}"}';
        } else {
            $query = '{"query": "mutation ($attachments: [Upload!]){updateEmail( id: \\"' . $targetId . '\\",input: {subject: \\"' . $email->getSubject() . '\\",messageId: \\"' . $email->getMessageId() . '\\",delivery:\\"' . $transform->transform($email->getDelivery()) . '\\",sender: \\"' . $email->getSender() . '\\",single: ' . json_encode($email->isSingle()) . ',priority: ' . $email->getPriority() . ',templateData: \\"' . $email->getTemplateData() . '\\",eId: \\"' . $email->getEId() . '\\",to: [\\"' . implode('","', $email->getTo()) . '\\"],cc: [\\"' . implode('\\",\\"', $email->getCc()) . '\\"],bcc: [\\"' . implode('\\",\\"', $email->getBcc()) . '\\"],text: \\"' . $text . '\\",textTemplate: \\"' . $email->getTextTemplate() . '\\",html: \\"' . $email->getHtml() . '\\",htmlTemplate: \\"' . $email->getHtmlTemplate() . '\\" ,project: \\"' . $email->getProject() . '\\",replyTo: \\"' . $email->getReplyTo() . '\\",attachments: $attachments}) {id}}"}';
        }

        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJlbWFpbCI6ImFkbWluQG9udGF2aW8uZGUiLCJpYXQiOjE2NjMyMjU3ODV9.NwZlzKYaSBAt4xpJxt-6g0qmtDZBELzfvx-TgiFaHuw',
        ];
        $options = [
            'multipart' => [
                [
                    'name' => 'operations',
                    'contents' => $query,
                ],
            ]];

        //push file map depending on how many uploads are defined
        $map = '{';
        for ($i = 0; count($email->getAttachments()) > $i; $i++) {
            $map .= '"file' . $i . '":["variables.attachments.' . $i . '"]';
            //dont append , for last in array
            if ($i != count($email->getAttachments()) - 1) {
                $map .= ',';
            }
        }
        $map .= '}';

        array_push($options['multipart'], [
            'name' => 'map',
            'contents' => $map,
        ]);

        //push uploads as multipart form file
        $loop = 0;
        foreach ($email->getAttachments() as $file) {
            array_push($options['multipart'], [
                'name' => 'file' . $loop,
                'contents' => Psr7\try_fopen($file->getFilePath(), 'r'),
                'filename' => $file->getFileName(),
                'headers' => [
                    'Content-Type' => '<Content-type header>',
                ],
            ]);
            $loop++;
        };

        $request = new Request('POST', $this->endpoint, $headers);
        $result = json_decode($client->sendAsync($request, $options)->wait()->getBody()->getContents());

        if (array_key_exists("errors", $result)) {
            return null;
        }
        return $result;
    }

    /**
     * @param string  $auth
     * @param string  $emailId
     *
     * @return string
     */
    public function read(string $emailEId)
    {
        //missing "project" and "stack" field, not needed currently
        $query = '{"query": "query{ getEmailViaEId( eId: \\"' . $emailEId . '\\"){ attachments{id chunkSize contentType filename length uploadDate} attachDataUrls bcc cc delivery eId html htmlTemplate messageIds priority replyTo sender single subject templateData text textTemplate to } } " }';

        $result = $this->executeQuery($this->endpoint, $query, $this->auth)->data;

        if (array_key_exists('getEmailViaEId', $result ?? [])) {
            $result = $result->getEmailViaEId;
            return new Email($result->attachments, $result->attachDataUrls ?? false, $result->bcc, $result->cc, $result->delivery, $result->eId, $result->html, $result->htmlTemplate, '"' . implode(",", $result->messageIds) . '"', $result->priority, "", $result->replyTo, $result->sender, $result->single, "", $result->subject, $result->templateData, $result->text, $result->textTemplate, $result->to);
        }
        return null;
    }

    /**
     * @param string  $emailEId
     *
     * @return mixed
     */
    public function readStatus(string $emailEId)
    {
        $query = '{"query": "query{ getEmailViaEId( eId: \\"' . $emailEId . '\\"){ sent rejected status } } " }';
        $result = $this->executeQuery($this->endpoint, $query, $this->auth)->data;

        if (array_key_exists('getEmailViaEId', $result ?? [])) {
            $result = $result->getEmailViaEId;
            return $result;
        }
        return null;
    }

    /**
     * @param string  $emailEId
     *
     * @return mixed
     */
    public function getEmailId(string $emailEId)
    {
        $query = '{"query": "query{ getEmailViaEId( eId: \\"' . $emailEId . '\\"){ id } } " }';
        $result = $this->executeQuery($this->endpoint, $query, $this->auth)->data;

        if (array_key_exists('getEmailViaEId', $result ?? [])) {
            $result = $result->getEmailViaEId->id;
            return $result;
        }
        return null;
    }

    /**
     * @param string  $emailId
     *
     * @return string
     */
    public function delete(string $emailEId)
    {
        $databaseId = $this->getEmailId($emailEId);
        $query = '{"query": "mutation{ deleteEmail(id: \\"' . $databaseId . '\\"){id eId} } " }';
        if ($databaseId != null) {
            return $this->executeQuery($this->endpoint, $query, $this->auth)->data->deleteEmail;
        }
        return null;
    }
}

