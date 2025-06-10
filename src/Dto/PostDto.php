<?php
namespace App\Dto;

use App\Entity\Post;
use Symfony\Component\Security\Core\User\UserInterface;
use JsonSerializable;

class PostDto implements JsonSerializable
{
    private Post $post;
    private ?UserInterface $currentUser;

    public function __construct(Post $post, ?UserInterface $currentUser = null)
    {
        $this->post = $post;
        $this->currentUser = $currentUser;
    }

    public static function fromPost(Post $post, ?UserInterface $user = null): self
    {
        return new self($post, $user);
    }

    public function jsonSerialize(): array
    {
        $likes    = $this->post->getLikes();
        $comments = $this->post->getComments();

        $commentData = [];
        foreach ($comments as $c) {
            $commentData[] = [
                'id'        => $c->getId(),
                'content'   => $c->getContent(),
                'author'    => $c->getUser()?->getUserIdentifier() ?? 'Anónimo',
                'createdAt' => $c->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        $userLiked = false;
        if ($this->currentUser) {
            $userLiked = $likes->exists(fn($i, $l) => $l->getUser() === $this->currentUser);
        }

        return [
            'id'                 => $this->post->getId(),
            'title'              => $this->post->getTitle(),
            'snippet'            => substr($this->post->getContent(), 0, 100),
            'content'            => $this->post->getContent(),
            'media'              => $this->post->getMedia(),
            'author'             => $this->post->getUser()?->getUserIdentifier(),
            'authorId'           => $this->post->getUser()?->getId(),
            'createdAt'          => $this->post->getCreatedAt()?->format('Y-m-d H:i:s'),
            'likes'              => count($likes),
            'commentsCount'      => count($comments),
            'comments'           => $commentData,
            'avatar'             => $this->post->getUser()?->getAvatar(),
            'likedByCurrentUser' => $userLiked,
        ];
    }
}
