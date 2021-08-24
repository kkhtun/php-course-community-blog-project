Database Structure

users
====
id
name
slug
email
password
image - nullable

languages
=========
id
name 
slug

categories
==========
id
name
slug

articles
========
id
category_id
user_id
title
slug
image
description

article_language
================
id
article_id
language_id

article_likes
=============
id
user_id
article_id

article_comments
================
id
user_id
article_id
comment



ajax API design
==============
like - api.php?like&article_id=12&user_id=12

Homework
========
Put Delete/Update post on authorized user - DONE
Your Posts tab on navbar - DONE
Make a working like button on index page - DONE


Somethings to look for
=====================
file isset($_FILES['image']) always resolving to true on user update
is it possible to group files into folders? links are gone lol
need to change slug of articles/users on update?
UI problem? page getting to top again after every page request, need to solve with frontend framework?