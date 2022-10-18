<?php

namespace Quicksilver;

use Symfony\Component\Validator\Constraints\DateTime;


class Email
{
    private $attachments;
    private $attachDataUrls;
    private $bcc; //string array
    private $cc; //string array
    private $delivery; //Datetime
    private $eId;
    private $html;
    private $htmlTemplate;
    private $messageId;
    private $priority; // enum: LOW NORMAL HIGH
    private $project;
    private $replyTo;
    private $sender;
    private $single;
    private $stack; //EmailStackItemInput
    private $subject;
    private $templateData;
    private $text;
    private $textTemplate;
    private $to; //string array

    /**
     * @param         $attachments
     * @param bool    $attachDataUrls
     * @param array   $bcc
     * @param array   $cc
     * @param         $delivery
     * @param string  $eId
     * @param string  $html
     * @param string  $htmlTemplate
     * @param string  $messageId
     * @param         $priority
     * @param string  $project
     * @param string  $replyTo
     * @param string  $sender
     * @param bool    $single
     * @param         $stack
     * @param string  $subject
     * @param string  $templateData
     * @param string  $text
     * @param string  $textTemplate
     * @param array   $to
     */
    public function __construct( $attachments, bool $attachDataUrls, array $bcc, array $cc, $delivery, string $eId, string $html, string $htmlTemplate, string $messageId, $priority, string $project, string $replyTo, string $sender, bool $single, $stack, string $subject, string $templateData, string $text, string $textTemplate, array $to)
    {
        $this->attachments = $attachments;
        $this->attachDataUrls = $attachDataUrls;
        $this->bcc = $bcc;
        $this->cc = $cc;
        $this->delivery = $delivery;
        $this->eId = $eId;
        $this->html = $html;
        $this->htmlTemplate = $htmlTemplate;
        $this->messageId = $messageId;
        //priority can be LOW,NORMAL or HIGH 
        if ($priority === "LOW" || $priority === "HIGH") {
            $this->priority = $priority;
        } else {
            $this->priority = "NORMAL";
        }
        $this->project = $project;
        $this->replyTo = $replyTo;
        $this->sender = $sender;
        $this->single = $single;
        $this->stack = $stack;
        $this->subject = $subject;
        $this->templateData = $templateData;
        $this->text = $text;
        $this->textTemplate = $textTemplate;
        $this->to = $to;
    }

    /**
     * @param mixed  $attachments
     *
     * @return Email
     */
    public function setAttachments($attachments): Email
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * @param bool  $attachDataUrls
     *
     * @return Email
     */
    public function setAttachDataUrls(bool $attachDataUrls): Email
    {
        $this->attachDataUrls = $attachDataUrls;
        return $this;
    }

    /**
     * @param array  $bcc
     *
     * @return Email
     */
    public function setBcc(array $bcc): Email
    {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @param array  $cc
     *
     * @return Email
     */
    public function setCc(array $cc): Email
    {
        $this->cc = $cc;
        return $this;
    }

    /**
     * @param mixed  $delivery
     *
     * @return Email
     */
    public function setDelivery($delivery): Email
    {
        $this->delivery = $delivery;
        return $this;
    }

    /**
     * @param string  $eId
     *
     * @return Email
     */
    public function setEId(string $eId): Email
    {
        $this->eId = $eId;
        return $this;
    }

    /**
     * @param string  $html
     *
     * @return Email
     */
    public function setHtml(string $html): Email
    {
        $this->html = $html;
        return $this;
    }

    /**
     * @param string  $htmlTemplate
     *
     * @return Email
     */
    public function setHtmlTemplate(string $htmlTemplate): Email
    {
        $this->htmlTemplate = $htmlTemplate;
        return $this;
    }

    /**
     * @param string  $messageId
     *
     * @return Email
     */
    public function setMessageId(string $messageId): Email
    {
        $this->messageId = $messageId;
        return $this;
    }

    /**
     * @param string  $priority
     *
     * @return Email
     */
    public function setPriority(string $priority): Email
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @param string  $project
     *
     * @return Email
     */
    public function setProject(string $project): Email
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @param string  $replyTo
     *
     * @return Email
     */
    public function setReplyTo(string $replyTo): Email
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    /**
     * @param string  $sender
     *
     * @return Email
     */
    public function setSender(string $sender): Email
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @param bool  $single
     *
     * @return Email
     */
    public function setSingle(bool $single): Email
    {
        $this->single = $single;
        return $this;
    }

    /**
     * @param mixed  $stack
     *
     * @return Email
     */
    public function setStack($stack): Email
    {
        $this->stack = $stack;
        return $this;
    }

    /**
     * @param string  $subject
     *
     * @return Email
     */
    public function setSubject(string $subject): Email
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @param string  $templateData
     *
     * @return Email
     */
    public function setTemplateData(string $templateData): Email
    {
        $this->templateData = $templateData;
        return $this;
    }

    /**
     * @param string  $text
     *
     * @return Email
     */
    public function setText(string $text): Email
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param string  $textTemplate
     *
     * @return Email
     */
    public function setTextTemplate(string $textTemplate): Email
    {
        $this->textTemplate = $textTemplate;
        return $this;
    }

    /**
     * @param array  $to
     *
     * @return Email
     */
    public function setTo(array $to): Email
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @return bool
     */
    public function isAttachDataUrls(): bool
    {
        return $this->attachDataUrls;
    }

    /**
     * @return array
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * @return array
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @return mixed
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * @return string
     */
    public function getEId(): string
    {
        return $this->eId;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @return string
     */
    public function getHtmlTemplate(): string
    {
        return $this->htmlTemplate;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getPriority(): string
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getProject(): string
    {
        return $this->project;
    }

    /**
     * @return string
     */
    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    /**
     * @return string
     */
    public function getSender(): string
    {
        return $this->sender;
    }

    /**
     * @return bool
     */
    public function isSingle(): bool
    {
        return $this->single;
    }

    /**
     * @return mixed
     */
    public function getStack()
    {
        return $this->stack;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getTemplateData(): string
    {
        return $this->templateData;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getTextTemplate(): string
    {
        return $this->textTemplate;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

}
