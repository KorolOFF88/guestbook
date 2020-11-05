<?php declare(strict_types = 1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback!');
    }

    public function testConferencePage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertCount(3, $crawler->filter('h4'));

        $client->clickLink('View');

        $this->assertPageTitleContains('Moscow');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Moscow 2020');
        $this->assertSelectorExists('div:contains("There are 1 comments")');
    }

    public function testCommentSubmission()
    {
        $client = static::createClient();
        $client->request('GET', '/conference/paris-2020');
        $client->submitForm('Submit', [
            'comment_form[author]' => 'Korolev Vladimir',
            'comment_form[email]' => 'korolev-test1@list.ru',
            'comment_form[photo]' => dirname(__DIR__, 2) . '/public/images/under-construction.gif',
            'comment_form[text]' => 'Some feedback from an automated functional test',
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorExists('div:contains("There are 1 comments.")');
    }
}
