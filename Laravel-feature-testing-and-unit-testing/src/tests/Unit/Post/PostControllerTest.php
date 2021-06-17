<?php

namespace Tests\Unit\Post;

use App\Modules\Post\Core\Domain\Repository\PostRepository;
use App\Modules\Post\Infrastructure\Repository\MySQLPostRepository;
use Mockery;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    public function testStoreDataPost()
    {
        $repo = Mockery::mock(MySQLPostRepository::class);

        $repo->shouldReceive('store')->once();
        app()->instance(PostRepository::class, $repo);
        $response = $this->post('/post', [
            '_token' => csrf_token(),
            'title' => 'test',
            'description' => 'description'
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/post');
    }

    public function testStoreDataFailedPost()
    {
        $repo = Mockery::mock(MySQLPostRepository::class);

        $repo->shouldReceive('store');
        app()->instance(PostRepository::class, $repo);
        $response = $this->post('/post', [
            'title' => ''
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    public function testRenderAllPostsPage()
    {
        $response = $this->get('/post');
        $response->assertStatus(200);
        $response->assertSeeText('Blog Post');
    }

    public function testRenderShowOnePostPage()
    {
        $response = $this->get('/post/dasar');
        $response->assertStatus(200);
        $response->assertSeeText('Dasar');
    }
}
