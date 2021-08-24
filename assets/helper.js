function addLikeEvent(likeNode) {
    likeNode.addEventListener('click', function() {
        var user_id = this.getAttribute('user_id')
        var article_id = this.getAttribute('article_id')
        if (user_id == 0) {
            location.href = "login.php";
        }

        fetch(`api.php?like&user_id=${user_id}&article_id=${article_id}`)
            .then((res) => res.json())
            .then((data) => {
                if (data.status === "liked") {
                    toastr.success("Article Liked")
                } else if (data.status === "unliked") {
                    toastr.warning("Article Unliked")
                }
                var like_count = this.childNodes[3];
                like_count.innerHTML = data.count;
            })
    })
}
