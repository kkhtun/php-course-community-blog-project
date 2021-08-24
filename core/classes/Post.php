<?php
class Post
{
    public static function all()
    {
        $articles = DB::table('articles')->orderBy('id', 'DESC')->paginate(3);
        foreach ($articles['data'] as $key => $obj) {
            $articles['data'][$key]->comment_count = DB::table('article_comments')->where('article_id', $obj->id)->count();
            $articles['data'][$key]->like_count = DB::table('article_likes')->where('article_id', $obj->id)->count();
        }
        return $articles;
    }

    public static function detail($slug)
    {
        $article = DB::table('articles')->where('slug', $slug)->getOne();
        // Get Langauges
        $article->languages = DB::raw("SELECT languages.id, languages.name, languages.slug FROM article_language 
                    LEFT JOIN languages
                    ON article_language.language_id = languages.id
                    WHERE article_language.article_id = $article->id")->get();

        // Show Comments
        $article->comments = DB::table('article_comments')->where('article_id', $article->id)->get();
        // Get Category
        $article->category = DB::table('categories')->where('id', $article->category_id)->getOne();

        // Comment Count
        $article->comment_count = count($article->comments);
        // Like Count
        $article->like_count = DB::table('article_likes')->where('article_id', $article->id)->count();

        return $article;
    }

    public static function articlesByCategory($slug)
    {
        $category = DB::table('categories')->where('slug', $slug)->getOne();
        if (!$category) {
            return false;
        }
        $articles = DB::table('articles')->where('category_id', $category->id)->orderBy('id', 'DESC')->paginate(3, "category=$slug");
        foreach ($articles['data'] as $key => $obj) {
            $articles['data'][$key]->comment_count = DB::table('article_comments')->where('article_id', $obj->id)->count();
            $articles['data'][$key]->like_count = DB::table('article_likes')->where('article_id', $obj->id)->count();
        }
        return $articles;
    }

    public static function articlesByLanguage($slug)
    {
        $language = DB::table('languages')->where('slug', $slug)->getOne();
        if (!$language) {
            return false;
        }
        $articles = DB::raw("SELECT articles.* FROM articles 
                            INNER JOIN
                            article_language
                            ON
                            articles.id = article_language.article_id
                            WHERE article_language.language_id = $language->id")->orderBy('articles.id', 'DESC')->paginate(3, "language=$slug");
        foreach ($articles['data'] as $key => $obj) {
            $articles['data'][$key]->comment_count = DB::table('article_comments')->where('article_id', $obj->id)->count();
            $articles['data'][$key]->like_count = DB::table('article_likes')->where('article_id', $obj->id)->count();
        }
        return $articles;
    }

    public static function articleByAuthor($slug)
    {
        $user = DB::table('users')->where('slug', $slug)->getOne();
        if (!$user) {
            return false;
        }
        $articles = DB::table('articles')->where('user_id', $user->id)->orderBy('id', 'DESC')->paginate(3, "author=$slug");
        foreach ($articles['data'] as $key => $obj) {
            $articles['data'][$key]->comment_count = DB::table('article_comments')->where('article_id', $obj->id)->count();
            $articles['data'][$key]->like_count = DB::table('article_likes')->where('article_id', $obj->id)->count();
        }
        return $articles;
    }

    public static function search($search)
    {
        $articles = DB::table('articles')->where('title', 'like', "%$search%")->orderBy('id', 'DESC')->paginate(3, "search=$search");
        foreach ($articles['data'] as $key => $obj) {
            $articles['data'][$key]->comment_count = DB::table('article_comments')->where('article_id', $obj->id)->count();
            $articles['data'][$key]->like_count = DB::table('article_likes')->where('article_id', $obj->id)->count();
        }
        return $articles;
    }

    public function create($request, $files)
    {
        // Validation Needed
        $error = $this->validateInput($request);

        // Upload Image 
        $image = $files['image'];
        if (!Helper::checkAllowedType($image['name'])) {
            $error[] = "File type not allowed";
        }
        if (count($error)) {
            return $error;
        }

        $targetPath = 'assets/article/' . mt_rand() . $image['name'];
        $tmpPath = $image['tmp_name'];
        if (move_uploaded_file($tmpPath, $targetPath)) {
            // Insert into DB
            $createdArticle = DB::create('articles', [
                "user_id" => User::auth()->id,
                "category_id" => $request['category_id'],
                "slug" => Helper::makeSlug($request['title']),
                "title" => $request['title'],
                "image" => $targetPath,
                "description" => $request['description']
            ]);

            // Insert Many to Many
            if ($createdArticle) {
                foreach ($request['language_id'] as $lang_id) {
                    DB::create('article_language', [
                        "article_id" => $createdArticle->id,
                        "language_id" => $lang_id
                    ]);
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
        // Return
    }

    public function validateInput($request, $reqType = 'create')
    {
        $error = [];
        if (isset($request)) {
            // Validate Title
            if (empty($request['title'])) {
                $error[] = "Title field is required";
            }

            // Validate Category
            if (empty($request['category_id'])) {
                $error[] = "Category is required";
            }

            // Validate Langauge
            if (empty($request['language_id']) || !is_array($request['language_id'])) {
                $error[] = "Language is required";
            }

            // Validate Description
            if (empty($request['description'])) {
                $error[] = "Description is required";
            }
            return $error;
        }
    }

    public function update($request, $files)
    {
        // Validation Needed
        $error = $this->validateInput($request);

        // Check if slug exists
        $slug = $request['post_slug'];
        $article = DB::table('articles')->where('slug', $slug)->getOne();
        // If article not found or unauthorized edit access occur
        $authUser = User::auth();
        if (!$article || $article->user_id !== $authUser->id) {
            $error[] = "Unknown Article";
        }
        // Return from function if errors exists
        if (count($error)) {
            return $error;
        }
        // Upload only when image is fully uploaded from client side
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $image = $files['image'];
            if (!Helper::checkAllowedType($image['name'])) {
                $error[] = "File type not allowed";
                return $error;
            }
            $targetPath = 'assets/article/' . mt_rand() . $image['name'];
            $tmpPath = $image['tmp_name'];
            $uploadStatus = move_uploaded_file($tmpPath, $targetPath);
            if (!$uploadStatus) {
                $error[] = "File upload error";
                return $error;
            }
            Helper::removeFile($article->image);
        } else {
            $targetPath = $article->image;
        }

        // Update into DB
        $updatedArticle = DB::update('articles', [
            "user_id" => $authUser->id,
            "category_id" => $request['category_id'],
            "title" => $request['title'],
            "image" => $targetPath,
            "description" => $request['description']
        ], $article->id);

        // Update Many to Many, is there a better way?
        if ($updatedArticle) {
            DB::raw("DELETE FROM article_language WHERE article_id = $updatedArticle->id")->get();
            foreach ($request['language_id'] as $lang_id) {
                DB::create('article_language', [
                    "article_id" => $updatedArticle->id,
                    "language_id" => $lang_id
                ]);
            }
            return true;
        } else {
            return ["Something Wrong"];
        }
    }

    public function delete($request)
    {
        $error = [];
        // Check if slug exists
        $slug = $request['post_slug'];
        $article = DB::table('articles')->where('slug', $slug)->getOne();
        // If article not found or unauthorized edit access occur
        $authUser = User::auth();
        if (!$article || $article->user_id !== $authUser->id) {
            $error[] = "Unknown Article";
        }
        // Return from function if errors exists
        if (count($error)) {
            return $error;
        }

        // Delete article from DB
        $dbDelete = DB::delete('articles', $article->id);
        // Delete article likes
        DB::raw("DELETE FROM article_likes")->where('article_id', $article->id)->get();
        // Delete article comments
        DB::raw("DELETE FROM article_comments")->where('article_id', $article->id)->get();
        // Delete article languages
        DB::raw("DELETE FROM article_language")->where('article_id', $article->id)->get();
        // Delete Image
        Helper::removeFile($article->image);
        // Return
        return $dbDelete ? true : ["Something wrong"];
    }
}
