<?php
require_once 'core/autoload.php';
$authUser = User::auth();
if (!$authUser) {
    Helper::redirect('login.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user'])) {
    $slug = $_GET['user'];
    $user = DB::table('users')->where('slug', $slug)->getOne();
    if (!$user || $user->id !== $authUser->id) {
        Helper::redirect('404.php');
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    $res = $user->update($_POST, $_FILES);
} else {
    Helper::redirect('404.php');
}

require_once 'inc/header.php';
?>

<div class="card card-dark">
    <div class="card-header bg-warning">
        <h3>Edit Profile</h3>
    </div>
    <div class="card-body">
        <form action="edit.php?user=<?php echo $user->slug ?>" method="post" enctype="multipart/form-data">
            <?php if (isset($res) and $res === true) : ?>
                <div class="alert alert-success">
                    User Updated Successfully
                </div>
                <?php
            elseif (isset($res) and is_array($res)) :
                foreach ($res as $e) :
                ?>
                    <div class="alert alert-danger">
                        <?php echo $e ?>
                    </div>
            <?php
                endforeach;
            endif;
            ?>
            <input type="hidden" name="user_slug" value="<?php echo $user->slug ?>">
            <div class="form-group">
                <label for="" class="text-white">Enter Username</label>
                <input type="text" name="name" class="form-control" placeholder="enter username" value="<?php echo $user->name ?>">
            </div>
            <div class="form-group">
                <label for="" class="text-white">Enter Email</label>
                <input type="text" name="email" class="form-control" placeholder="enter email" value="<?php echo $user->email ?>">
            </div>
            <div class="form-group">
                <label for="" class="text-white">Enter Password</label>
                <input type="password" name="password" class="form-control" placeholder="enter password">
            </div>
            <div class="form-group">
                <label for="" class="text-white">Choose Image</label>
                <input type="file" name="image" class="form-control">
                <img src="<?php echo $user->image ?>" style="width: 200px; border-radius: 20px;" alt="">
            </div>
            <input type="submit" value="Update" class="btn btn-outline-warning">
        </form>
    </div>
</div>
<?php
require_once 'inc/footer.php'; ?>