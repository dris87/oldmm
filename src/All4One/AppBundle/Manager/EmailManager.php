<?php

/*
 * This file is part of the `All4One/Ujallas.hu` project.
 *
 * (c) https://ujallas.hu
 *
 * Developed by: Ferencz Dávid Tamás <fdt0712@gmail.com>
 * Contributed: Sipos Zoltán <sipiszoty@gmail.com>, Pintér Szilárd <leaderlala00@gmail.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace All4One\AppBundle\Manager;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class EmailManager.
 */
class EmailManager extends AbstractManager
{
    /**
     * @var string
     */
    private $messagePrefix = 'email';

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $emailSender;

    /**
     * EmailManager constructor.
     *
     * @param string              $emailSender
     * @param \Swift_Mailer       $mailer
     * @param TranslatorInterface $translator
     * @param \Twig_Environment   $twig_Environment
     */
    public function __construct(string $emailSender, \Swift_Mailer $mailer, TranslatorInterface $translator, \Twig_Environment $twig_Environment)
    {
        $this->emailSender = $emailSender;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->twig = $twig_Environment;
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $token
     * @param array  $data
     * @param string|null $replyTo
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function send(string $from, string $to, string $token, array $data = [], ?string $replyTo = null)
    {
        $title = $this->translator->trans($this->messagePrefix.'.'.$token.'.title');
        $message = ( new \Swift_Message($title) )
            ->setFrom($from)
            ->setTo($to)
            ->setBody(
                $this->twig->render(
                    'emails/'.str_replace('.', '\\', $token).'.html.twig',
                    $data
                ),
                'text/html'
            );

        if ($replyTo !== null) {
            $message->setReplyTo($replyTo);
        }

        $this->mailer->send($message);
    }

    /**
     * @return string
     */
    public function getDefaultSender()
    {
        return $this->emailSender;
    }
}
