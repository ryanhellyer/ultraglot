
Problems:
At what point is the group ID created.
If we create the group ID when the first post is published.


** List of posts in meta box **
If current post already has a group ID, then need to check if post already has a group ID - don't display if it already has one (which doesn't match the current posts group ID)
If the current post doesn't have a group ID, then need to grab group ID from the other post.
If neither post have group ID's, then need to set group ID for both of them.


** Database setup from Cristi **
wp_language_mapping:
    language_group_id int(20) … arbitrary number that groups posts that have the same content, in different languages (like a pointer to a term id, without the term in the db). Not unique.
    language_post_id int(20) … points to a blog post in a certain language
    language_blog_id int(20) … points to the blog where the post is


** Stuff to do **
create_table() should only fire if the table doesn't exist and only in the admin panel

