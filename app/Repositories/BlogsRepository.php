<?php

namespace App\Repositories;

use App\Models\Blog;
use App\Models\EmailQueue;
use App\Models\Tag;
use App\User;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Throwable;

class BlogsRepository
{
    /**
     * @var User
     */
    private $user;

    /**
     * BlogsRepository constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    const VALID_MODERATION_STATUSES = [
        Blog::DB_MODERATION_STATUS_APPROVED,
        Blog::DB_MODERATION_STATUS_REJECTED,
        Blog::DB_MODERATION_STATUS_PENDING,
    ];

    /**
     * @param Blog $blog
     * @param array $moderationData
     * @return bool
     * @throws Throwable
     */
    public function moderateOrFail(Blog $blog, array $moderationData): bool
    {
        $moderationData = $this->validateAndExtractModerationData($blog, $moderationData);

        $moderationData['moderated_by'] = $this->user->id;
        $moderationData['moderated_at'] = date('Y-m-d H:i:s');

        $blog->fill($moderationData);

        return $blog->saveOrFail();
    }

    /**
     * @param Blog $blog
     * @param array $moderationData
     * @return array
     * @throws Exception
     */
    private function validateAndExtractModerationData(Blog $blog, array $moderationData): array
    {

        $newModerationStatus = $moderationData['moderation_status'] ?? null;
        $moderationNotes = $moderationData['moderation_notes'] ?? null;

        if (!$this->user->isAdmin()) {
            throw new Exception("You don't have permission to moderate blogs");
        }

        if (!in_array($newModerationStatus, self::VALID_MODERATION_STATUSES)) {
            throw new Exception("Invalid status {$newModerationStatus} given");
        }

        if ($newModerationStatus === $blog->moderation_status) {
            throw new Exception("Moderation status is already in {$newModerationStatus} state.");
        }

        if (empty($moderationNotes) || strlen($moderationNotes) < 8) {
            throw new Exception("Moderation notes must be at least 8 characters");
        }


        return Arr::only($moderationData, ['moderation_status', 'moderated_by', 'moderated_at', 'moderation_notes']);
    }

    /**
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total' => Blog::query()->count(),
            'status_draft' => Blog::query()->where('status', Blog::DB_STATUS_DRAFT)->count(),
            'status_published' => Blog::query()->where('status', Blog::DB_STATUS_PUBLISHED)->count(),
            'moderation_pending' => Blog::query()
                ->where('moderation_status', Blog::DB_MODERATION_STATUS_PENDING)
                ->count(),
            'moderation_approved' => Blog::query()
                ->where('moderation_status', Blog::DB_MODERATION_STATUS_APPROVED)
                ->count(),
            'moderation_rejected' => Blog::query()
                ->where('moderation_status', Blog::DB_MODERATION_STATUS_REJECTED)
                ->count(),
        ];
    }

    /**
     * @param User $loggedInUser
     * @param array $data
     * @return Blog
     * @throws ValidationException
     */
    public function storeBlog(User $loggedInUser, array $data): Blog
    {

        $blog = null;

        try {

            // todo: future - can add blog hash for content changes
            // todo: future - can always create new entry instead of updating
            DB::beginTransaction();

            $id = $data['id'] ?? null;
            $editMode = !empty($id);

            // 1. Save actual Blog entry
            if ($editMode) {

                $blog = Blog::query()->findOrFail($id);

                if (!$loggedInUser->isBlogModerator()) {
                    // User updates content, let moderators re-moderate the blog again
                    $data['moderation_status'] = Blog::DB_MODERATION_STATUS_PENDING;
                }


            } else {

                $data['user_id'] = $loggedInUser->id;
                $data['moderation_status'] = Blog::DB_MODERATION_STATUS_PENDING;

                if ($loggedInUser->isBlogModerator()) {
                    $data['moderation_status'] = Blog::DB_MODERATION_STATUS_APPROVED;
                    $data['moderation_notes'] = "Blog is pre-approved by the system";
                    $data['moderated_at'] = mysql_now();
                    $data['moderated_by'] = $loggedInUser->id;
                }

                $blog = new Blog;
            }

            $blog->fill($data);
            $blog->saveOrFail();

            // 2. Save Blog tags
            $tags = $data['tags'] ?? null;

            if (empty($tags) || !is_string($tags)) {
                throw new InvalidArgumentException("Tag(s) csv required (eg: 'php, git, first-post')");
            }

            $tags = trim($tags, "\t\n\r\0\x0B,");
            $tagsArr = explode(',', $tags);

            if (empty($tagsArr)) {
                throw new InvalidArgumentException("Tag(s) csv required (eg: 'php, git, first-post')");
            }

            $tagIds = [];
            foreach ($tagsArr as $tagName) {
                $sanitizedTag = trim(strtolower($tagName));

                if (empty($sanitizedTag)) {
                    continue; // skipping these
                }

                $tag = Tag::findOrNewByName($sanitizedTag); // throws on error
                $tagIds[] = $tag->id;
            }

            if (empty($tagIds)) {
                throw new InvalidArgumentException("Unable to save tags.");
            }

            // add / remove tags from pivot table (blog_tags)
            $blog->tags()->sync($tagIds);

            // 3. Send emails to moderator (if applicable)
            if (!$loggedInUser->isBlogModerator() && $blog->moderation_status === Blog::DB_MODERATION_STATUS_PENDING) {
                $this->sendModerationEmails($blog);
            }

            DB::commit();


        } catch (Throwable $throwable) {

            DB::rollBack();
            throw ValidationException::withMessages([
                'errors' => [$throwable->getMessage()]
            ]);
        }

        return $blog;
    }

    /**
     * Send emails to moderator
     * @param Blog $blog
     * @throws Throwable
     */
    private function sendModerationEmails(Blog $blog)
    {

        /** @var User[] $moderators */
        $moderators = User::getBlogModerators();

        if (!empty($moderators)) {

            $userFullName = $this->user->name();
            $blogPostsLink = get_site_url() . '/blogs';
            $message = "{$userFullName} has created/modified blog post \"{$blog->title}\" and is ready for moderation <br/>"
                . "<br/>  Please visit the following link to moderate the blog post: "
                . "<a href='{$blogPostsLink}'>{$blogPostsLink}</a>";

            foreach ($moderators as $moderator) {

                $viewData = [
                    'name' => $moderator->name(),
                    'email_body' => $message,
                ];

                EmailQueue::queue(
                    $moderator->email,
                    "TrenchDevs: Blog Entry \"{$blog->title}\" Moderation",
                    $viewData,
                    'emails.generic'
                );
            }
        }
    }


}
