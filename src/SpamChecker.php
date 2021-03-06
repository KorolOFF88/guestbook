<?php declare(strict_types = 1);

namespace App;

use App\Entity\Comment;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class SpamChecker
 * @package App
 */
class SpamChecker
{
    /**
     * @var HttpClientInterface
     */
    private $_client;

    /**
     * @var string
     */
    private $_endpoint;

    /**
     * SpamChecker constructor.
     *
     * @param HttpClientInterface $client
     * @param string              $akismetKey
     */
    public function __construct(HttpClientInterface $client, string $akismetKey)
    {
        $this->_client = $client;
        $this->_endpoint = sprintf('https://%s.rest.akismet.com/1.1/comment-check', $akismetKey);
    }

    /**
     * @param Comment $comment
     * @param array   $context
     *
     * @return int Spam score: 0: not spam, 1: maybe spam, 2: blatant spam
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getSpamScore(Comment $comment, array $context): int
    {
        $response = $this->_client->request('POST', $this->_endpoint, [
            'body' => array_merge($context, [
                'blog' => 'http://guestbook.loc',
                'comment_type' => 'comment',
                'comment_author' => $comment->getAuthor(),
                'comment_author_email' => $comment->getEmail(),
                'comment_content' => $comment->getText(),
                'comment_date_gmt' => $comment->getCreatedAt()->format('c'),
                'blog_lang' => 'en',
                'blog_charset' => 'UTF-8',
                'is_test' => true,
            ]),
        ]);

        $headers = $response->getHeaders();
        if ('discard' === ($headers['x-akismet-pro-tip'][0] ?? '')) {
            return 2;
        }

        $content = $response->getContent();
        if (isset($headers['x-akismet-debug-help'][0])) {
            throw new \RuntimeException(sprintf('Unable to check for spam: %s (%s).', $content, $headers['x-akismet-debug-help'][0]));
        }

        return ('true' === $content) ? 1 : 0;
    }
}
