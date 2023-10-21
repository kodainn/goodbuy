<?php

namespace App\Repositories\Post;

use App\Models\TblPost;
use App\Models\TblPostImage;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Throwable;

class TblPostRepository
{

    private $tblPost;
    private $tblPostImage;

    public function __construct(
        TblPost $tblPost,
        TblPostImage $tblPostImage
    )
    {
        $this->tblPost = $tblPost;
        $this->tblPostImage = $tblPostImage;
    }

    public function getPostWithChild()
    {
        return $this->tblPost
                ->with(
                    ['images' => function ($query) {
                        $query->orderBy('image_sort', 'asc');
                    },
                    'goods']
                )
                ->withCount('goods')
                ->orderBy('created_at', 'desc')
                ->get();
    }

    public function getPostWithChildOfUser($user_uuid)
    {
        return $this->tblPost
                ->with(
                    ['images' => function($query) {
                        $query->orderBy('image_sort', 'asc');
                    },
                    'goods']
                )
                ->withCount('goods')
                ->where('user_uuid', '=', $user_uuid)
                ->orderBy('created_at', 'desc')
                ->get();
    }

    public function getPostWithChildFirst($post_uuid)
    {
        $search = [
            ['post_uuid', '=', $post_uuid]
        ];
        return $this->tblPost
                ->where($search)
                ->with(['images', 'user', 'goods'])
                ->withCount('goods')
                ->first();
    }

    public function getSearchPostWithChild($genre)
    {
        return $this->tblPost->where('genre_div', '=', $genre)
                ->with(
                    ['images' => function ($query) {
                        $query->orderBy('image_sort', 'asc');
                    },
                    'goods']
                )
                ->withCount('goods')
                ->orderBy('created_at', 'desc')
                ->get();
    }

    public function getPostCountOfUser($user_uuid)
    {
        $search = [
            ['user_uuid', '=', $user_uuid]
        ];

        return $this->tblPost
                ->where($search)
                ->count();
    }

    public function insertPostWithImages($request)
    {
        try {
            DB::beginTransaction();

            $post_uuid = Uuid::uuid4();
            $this->tblPost->insert([
                'post_uuid' => $post_uuid,
                'user_uuid' => $request->user_uuid,
                'title' => $request->title,
                'review' => $request->review,
                'genre_div' => $request->genreDiv
            ]);

            $insertList = [];
            $image_sort = 1;
            foreach($request->imageList as $image) {
                $newFilePath = $image[0]->store('public/img');
                $newFileName = explode('/', $newFilePath)[2];
                $insertList[] = [
                    'post_image_uuid' => Uuid::uuid4(),
                    'post_uuid' => $post_uuid,
                    'post_image_path' => '/storage/img/' . $newFileName,
                    'image_sort' => $image_sort,
                ];
                $image_sort++;
            }
            $this->tblPostImage->insert($insertList);

            DB::commit();
            $request->session()->regenerateToken();
        } catch(Throwable $e) {
            DB::rollBack();
            echo 'エラーメッセージ' . $e->getMessage();
        }
    }

    public function deletePost($post_uuid, $user_uuid)
    {
        $search = [
            ['post_uuid', '=', $post_uuid],
            ['user_uuid', '=', $user_uuid]
        ];
        $this->tblPost
            ->where($search)
            ->delete();
    }
}