<?php
namespace App\Services\Bot;

use App\Models\Forum;
use App\Models\ForumPost;

class ForumService
{
    public function getForums()
    {
        return Forum::all();
    }

    public function getPostsByForum($forumId)
    {
        return ForumPost::where('forum_id', $forumId)->get();
    }

    public function getPostByTitle($title)
    {
        return ForumPost::whereRaw(
            'LOWER(title) = ?',
            [strtolower($title)]
        )->first();
    }

    public function getForumsText(): string
    {
        $forums = $this->getForums();

        if ($forums->isEmpty()) {
            return "No forums available at the moment.";
        }

        $lines = ["💬 *Forums:*\n"];

        foreach ($forums as $forum) {
            $posts = ForumPost::where('forum_id', $forum->id)->get();
            $lines[] = "📁 *{$forum->name}*";  // ← name not title

            foreach ($posts as $post) {
                $lines[] = "  • *{$post->title}*";
                $lines[] = "    {$post->content}";  // ← content column
            }

            $lines[] = "";  // blank line between forums
        }

        return implode("\n", $lines);
    }
}
