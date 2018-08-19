<?php

class MainTest extends \Tests\TestCase
{

//    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /*
     *
     *
     *

    List of all main routes, and if they are covered by any tests:
    There might be some additional tests still to be written (For example we create a new post, but don't assign any categories to it at the moment)

    /blog/...
        blogetc.index                       YES
        blogetc.feed                        YES
        blogetc.view_category               no - but this is basically blogetc.index
        blogetc.single                      YES
        blogetc.comments.add_new_comment    YES

    /blog_admin/...
        blogetc.admin.index                 YES
        blogetc.admin.create_post           no - but is just a form
        blogetc.admin.store_post            YES
        blogetc.admin.edit_post             YES - but no extra checks
        blogetc.admin.update_post           YES
        blogetc.admin.destroy_post          YES

     /blog_admin/comments/...

            blogetc.admin.comments.index    YES
            blogetc.admin.comments.approve  YES
            blogetc.admin.comments.delete   YES

     /blog_admin/categories/...

            blogetc.admin.categories.index
            blogetc.admin.categories.create_category    no - but is just a form
            blogetc.admin.categories.store_category     YES
            blogetc.admin.categories.edit_category      no - but is just a form
            blogetc.admin.categories.update_category
            blogetc.admin.categories.destroy_category   YES

     *
     *
     *
     */

    public function testFilesArePresent()
    {
        $this->assertTrue(file_exists(config_path("blogetc.php")), "/config/blogetc.php should exist - currently no file with that filename is found");
        $this->assertTrue(is_array(include(config_path("blogetc.php"))), "/config/blogetc.php should exist - currently no file with that filename is found");
    }

    public function testImageSizesAreSane()
    {

        $this->assertTrue(count(\WebDevEtc\BlogEtc\Helpers::image_sizes()) == 3);

        foreach (\WebDevEtc\BlogEtc\Helpers::image_sizes() as $image_key => $image_info) {

            $this->assertArrayHasKey("w", $image_info);
            $this->assertArrayHasKey("h", $image_info);
            $this->assertArrayHasKey("name", $image_info);
            $this->assertArrayHasKey("enabled", $image_info);

            $this->assertTrue(is_bool($image_info['enabled']));
            $this->assertTrue(is_int($image_info['w']));
            $this->assertTrue(is_int($image_info['h']));
            $this->assertTrue(is_string($image_info['name']));
            $this->assertTrue(is_string($image_key));
            $this->assertTrue(count($image_info) == 4);


        }

    }


    public function testUserHasNanManageBlogEtcPostsMethod()
    {

        $this->assertTrue(method_exists(\App\User::class, "canManageBlogEtcPosts"), "Your User model must have the canManageBlogEtcPosts method");

        $user = new \App\User();
        $this->assertTrue(is_bool($user->canManageBlogEtcPosts()));

    }

    // more tests coming soon

    public function testCanSeeAdminPanel()
    {

        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");

        // without a logged in user, should give error
        $response = $this->get($admin_panel_url);
        $response->assertStatus(401);


//        $user = new \App\User();


        $user = $this->create_admin_user();

        // check user can see admin area:
        $this->assertTrue($user->canManageBlogEtcPosts());

        $response = $this->get($admin_panel_url);
        // check if we can see the admin panel correctly
        $response->assertStatus(200);
        $response->assertSee("All Posts");
        $response->assertSee("Add Post");
        $response->assertSee("All Comments");
        $response->assertSee("All Categories");
        $response->assertSee("Add Category");


        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");
//        $user=$this->create_admin_user();

        $this->assertTrue($user->canManageBlogEtcPosts());

        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
        $new_object_vals['use_view_file'] = null;

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);


    }

    public function testCanCreatePost()
    {
        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");


        $user = $this->create_admin_user();

        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");

        $this->assertTrue($user->canManageBlogEtcPosts());

        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
        $new_object_vals['use_view_file'] = null;

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);


    }


    public function testCanCreatePostThenEditIt()
    {
        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");


        $user = $this->create_admin_user();

        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");

        $this->assertTrue($user->canManageBlogEtcPosts());

        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
        $new_object_vals['use_view_file'] = null;

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        $justCreatedRow = \WebDevEtc\BlogEtc\Models\BlogEtcPost::where("slug", $new_object_vals['slug'])->firstOrFail();


        $new_object_vals['title'] = "New title " . str_random();
        $this->assertDatabaseMissing('blog_etc_posts', ['title' => $new_object_vals['title']]);
        $response = $this->patch($admin_panel_url . "/edit_post/" . $justCreatedRow->id, $new_object_vals);
        $response->assertStatus(302);
        $this->assertDatabaseHas('blog_etc_posts', ['title' => $new_object_vals['title']]);


    }


    public function testCreatePostThenCheckIsViewableToPublic()
    {

        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");


        $user = $this->create_admin_user();

        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");

        $this->assertTrue($user->canManageBlogEtcPosts());

        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        $new_object_vals['slug'] = "slug123" . str_random();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
        $new_object_vals['use_view_file'] = null;

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);


        // check we don't see it at moment
        $response = $this->get(config("blogetc.blog_prefix", "blog"));
        $response->assertDontSee($new_object_vals['slug']);

        // must clear the cache, as the /feed is cached
        \Artisan::call('cache:clear');

        $response = $this->get(config("blogetc.blog_prefix", "blog") . "/feed");
        $response->assertDontSee($new_object_vals['slug']);

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        $response = $this->get(config("blogetc.blog_prefix", "blog"));
        // if we see the slug (which is str_random()) we can safely assume that there was a link to the post, so it is working ok. of course it would depend a bit on your template but this should work.
        $response->assertSee($new_object_vals['slug']);


        // must clear the cache, as the /feed is cached
        \Artisan::call('cache:clear');

        $response = $this->get(config("blogetc.blog_prefix", "blog") . "/feed");
        $response->assertSee($new_object_vals['slug']);
        $response->assertSee($new_object_vals['title']);


        // now check single post is viewable

        $response = $this->get(route("blogetc.single", $new_object_vals['slug']));
        $response->assertStatus(200);
        $response->assertSee($new_object_vals['slug']);
        $response->assertSee($new_object_vals['title']);


    }



    public function testCreatePostWithNotPublishedThenCheckIsNotViewableToPublic()
    {


        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");
        list( $new_object_vals, $search_for_obj) = $this->prepare_post_creation();

        $new_object_vals['is_published'] = false;

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        // must log out, as the admin user can see posts dated in future
        \Auth::logout();

        $response = $this->get(config("blogetc.blog_prefix", "blog"));
        // if we see the slug (which is str_random()) we can safely assume that there was a link to the post, so it is working ok. of course it would depend a bit on your template but this should work.
        $response->assertDontSee($new_object_vals['slug']);


        // now check single post is viewable

        $response = $this->get(config("blogetc.blog_prefix", "blog") . "/" . $new_object_vals['slug']);
        $response->assertStatus(404);
        $response->assertDontSee($new_object_vals['slug']);
        $response->assertDontSee($new_object_vals['title']);


    }


    public function testCreatePostWithFuturePostedAtThenCheckIsNotViewableToPublic()
    {


        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");
        list( $new_object_vals, $search_for_obj) = $this->prepare_post_creation();

        $new_object_vals['posted_at'] = \Carbon\Carbon::now()->addMonths(12);

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);

        // must log out, as the admin user can see posts dated in future
        \Auth::logout();

        $response = $this->get(config("blogetc.blog_prefix", "blog"));
        // if we see the slug (which is str_random()) we can safely assume that there was a link to the post, so it is working ok. of course it would depend a bit on your template but this should work.
        $response->assertDontSee($new_object_vals['slug']);


        // now check single post is viewable

        $response = $this->get(config("blogetc.blog_prefix", "blog") . "/" . $new_object_vals['slug']);
        $response->assertStatus(404);
        $response->assertDontSee($new_object_vals['slug']);
        $response->assertDontSee($new_object_vals['title']);


    }


    public function testCreatePostThenCheckCanCreateCommentThenApproveComment()
    {

        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");


        $user = $this->create_admin_user();

        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");
        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
        $new_object_vals['use_view_file'] = null;

        $new_object_vals['_token'] = csrf_token();

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);


        if (config("blogetc.comments.type_of_comments_to_show") === 'built_in') {
            $comment_detail = [
                '_token' => csrf_token(),
                'author_name' => str_random(),
                'comment' => str_random(),
            ];
            $this->assertDatabaseMissing('blog_etc_comments', ['author_name' => $comment_detail['author_name']]);
            $response = $this->post(config("blogetc.blog_prefix", "blog") . "/save_comment/" . $new_object_vals['slug'], $comment_detail);
            $response->assertStatus(200);

            $default_approved = config("blogetc.comments.auto_approve_comments", true) == true ? 1 : 0;
            $this->assertDatabaseHas('blog_etc_comments', ['approved' => $default_approved, 'author_name' => $comment_detail['author_name']]);

            if ($default_approved == 0) {
                dump("By default comments are NOT approved (config(\"blogetc.comments.auto_approve_comments\") == false), so we will check the admin user can approve the comment just subitted");

                $justAddedRow = \WebDevEtc\BlogEtc\Models\BlogEtcComment::where('author_name', $comment_detail['author_name'])->firstOrFail();

                $response = $this->get(route("blogetc.admin.comments.index"));
                $response->assertSee($justAddedRow->author_name);


                // approve it:
                $response = $this->patch(route("blogetc.admin.comments.approve", $justAddedRow->id), [
                    '_token' => csrf_token(),
                ]);
                // check it was approved
                $response->assertStatus(302);

                $this->assertDatabaseHas('blog_etc_comments', ['approved' => 1, 'author_name' => $justAddedRow->author_name]);


            } else {
                dump("By default comments are auto approved (config(\"blogetc.comments.auto_approve_comments\" == true), so we don't check now if the admin user can approve it");
            }


        } else {
            dump("NOT TESTING COMMENT FEATURE, as config(\"blogetc.comments.type_of_comments_to_show\") is not set to 'built_in')");
        }

    }


    public function testCreatePostThenCheckCanCreateCommentThenDeleteComment()
    {
        $user = $this->create_admin_user();

        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");
        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
        $new_object_vals['use_view_file'] = null;

        $new_object_vals['_token'] = csrf_token();

        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);


        if (config("blogetc.comments.type_of_comments_to_show") === 'built_in') {
            $comment_detail = [
                '_token' => csrf_token(),
                'author_name' => str_random(),
                'comment' => str_random(),
            ];
            $this->assertDatabaseMissing('blog_etc_comments', ['author_name' => $comment_detail['author_name']]);
            $response = $this->post(config("blogetc.blog_prefix", "blog") . "/save_comment/" . $new_object_vals['slug'], $comment_detail);
            $response->assertStatus(200);

            $this->assertDatabaseHas('blog_etc_comments', ['author_name' => $comment_detail['author_name']]);


            $justAddedRow = \WebDevEtc\BlogEtc\Models\BlogEtcComment::where('author_name', $comment_detail['author_name'])->firstOrFail();

            // check the just added row exists...
            $response = $this->get(route("blogetc.admin.comments.index"));
            $response->assertSee($justAddedRow->author_name);


            // delete it:
            $response = $this->delete(route("blogetc.admin.comments.delete", $justAddedRow->id), [
                '_token' => csrf_token(),
            ]);
            // check it was deleted (it will deleted if approved)
            $response->assertStatus(302);

            //check it doesnt exist in database
            $this->assertDatabaseMissing('blog_etc_comments', ['id' => $justAddedRow->id,]);


        } else {
            dump("NOT TESTING COMMENT FEATURE, as config(\"blogetc.comments.type_of_comments_to_show\") is not set to 'built_in')");
        }

    }


    public function testCanCreateThenDeletePost()
    {
        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");


        $user = $this->create_admin_user();

        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");

        $this->assertTrue($user->canManageBlogEtcPosts());

        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
        $new_object_vals['use_view_file'] = null;

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);
        $response = $this->post($admin_panel_url . "/add_post", $new_object_vals);


        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_posts', $search_for_obj);


        $justCreatedRow = \WebDevEtc\BlogEtc\Models\BlogEtcPost::where("slug", $new_object_vals['slug'])->firstOrFail();
        $id = $justCreatedRow->id;
        $delete_url = $admin_panel_url . "/delete_post/" . $id;

        $response = $this->delete($delete_url, ['_token' => csrf_token()]);
        $response->assertStatus(200);

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);

    }


    public function testCanCreateCategory()
    {
        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");
        $this->create_admin_user();
        // now lets create a category
        $new_cat_vals = [
            'category_name' => str_random(),
            'slug' => str_random(),
        ];
        $search_for_new_cat = $new_cat_vals;
        $new_cat_vals['_token'] = csrf_token();
        $this->assertDatabaseMissing('blog_etc_categories', $search_for_new_cat);
        $response = $this->post($admin_panel_url . "/categories/add_category", $new_cat_vals);
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_categories', $search_for_new_cat);


    }


    public function testCanCreateCategoryThenEditIt()
    {


        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");


        $this->create_admin_user();
        // now lets create a category
        $new_cat_vals = [
            'category_name' => str_random(),
            'slug' => str_random(),
        ];


        // create a post so we can edit it later
        $search_for_new_cat = $new_cat_vals;
        $new_cat_vals['_token'] = csrf_token();
        $this->assertDatabaseMissing('blog_etc_categories', $search_for_new_cat);
        $response = $this->post($admin_panel_url . "/categories/add_category", $new_cat_vals);
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_categories', $search_for_new_cat);


        // get the just inserted row
        $justCreatedRow = \WebDevEtc\BlogEtc\Models\BlogEtcCategory::where("slug", $new_cat_vals['slug'])->firstOrFail();


        // get the edit page (form)
        $response = $this->get(
            $admin_panel_url . "/categories/edit_category/" . $justCreatedRow->id
        );
        $response->assertStatus(200);

        // create some edits...
        $new_object_vals['category_name'] = "New category name " . str_random();
        $new_object_vals['slug'] = $justCreatedRow->slug;
        $new_object_vals['_token'] = csrf_token();


        $this->assertDatabaseMissing('blog_etc_categories', ['category_name' => $new_object_vals['category_name']]);


        // send the request to save the changes
        $response = $this->patch(
            route("blogetc.admin.categories.update_category", $justCreatedRow->id),
            $new_object_vals
        );


        $response->assertStatus(302); // check it was a redirect

        // check that the edited category name is in the database.
        $this->assertDatabaseHas('blog_etc_categories', ['slug' => $new_object_vals['slug'], 'category_name' => $new_object_vals['category_name']]);


    }


    public function testCanDeleteCategory()
    {
        $admin_panel_url = config("blogetc.admin_prefix", "blog_admin");
        $this->create_admin_user();
        // now lets create a category
        $new_cat_vals = [
            'category_name' => str_random(),
            'slug' => str_random(),
        ];
        $search_for_new_cat = $new_cat_vals;
        $new_cat_vals['_token'] = csrf_token();
        $this->assertDatabaseMissing('blog_etc_categories', $search_for_new_cat);
        $response = $this->post($admin_panel_url . "/categories/add_category", $new_cat_vals);
        $response->assertStatus(302); // redirect
        $this->assertDatabaseHas('blog_etc_categories', $search_for_new_cat);


        $justCreatedRow = \WebDevEtc\BlogEtc\Models\BlogEtcCategory::where("slug", $new_cat_vals['slug'])->firstOrFail();
        $id = $justCreatedRow->id;

        $delete_url = $admin_panel_url . "/categories/delete_category/$id";

        $response = $this->delete($delete_url, ['_token' => csrf_token()]);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('blog_etc_categories', $search_for_new_cat);

    }

    /**
     * @return array
     */
    protected function generate_basic_blog_post_with_random_data()
    {
        $new_object_vals = [];

        foreach ([
                     'title',
                     'subtitle',
                     'slug',
                     'post_body',
                     'meta_desc',
                 ] as $field) {
            $new_object_vals[$field] = str_random();
        }
        return $new_object_vals;
    }

    /**
     * @return mixed
     */
    protected function create_admin_user()
    {


        $user = $this->getMockBuilder(\App\User::class)
            ->getMock();
        // make sure the user can see admin panel
        $user->method("canManageBlogEtcPosts")
            ->will($this->returnCallback(function () {
                return true;
            }));


        // set up some dummy info
        $user->name = str_random() . "testuser";
        $user->password = str_random();
        $user->email = str_random() . "@example.com";

        $this->actingAs($user);


        //get a page (for session/csrf) - do not delete this line!
        $response = $this->get("/");


        return $user;
    }

    /**
     * @return array
     */
    protected function prepare_post_creation()
    {
        $user = $this->create_admin_user();


        $this->assertTrue($user->canManageBlogEtcPosts());

        $new_object_vals = $this->generate_basic_blog_post_with_random_data();

        // to verify this was added to database. Use a different variable, so we can add things (like _token) and still be able to assertDatabaseHas later.
        $search_for_obj = $new_object_vals;

        $new_object_vals['is_published'] = 1;
        $new_object_vals['posted_at'] = \Carbon\Carbon::now();
        $new_object_vals['use_view_file'] = null;

        $new_object_vals['_token'] = csrf_token();

        $this->assertDatabaseMissing('blog_etc_posts', $search_for_obj);


        // check we don't see it at moment
        $response = $this->get(config("blogetc.blog_prefix", "blog"));
        $response->assertDontSee($new_object_vals['slug']);
        return array( $new_object_vals, $search_for_obj);
    }

}
