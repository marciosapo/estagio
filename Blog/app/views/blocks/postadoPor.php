<p class="text-muted mt-4">
    Postado por 
    <strong>
        <a href="Blog/verUser?userId=<?php echo urlencode($post['id_user']); ?>&username=<?php echo urlencode($post['postado']); ?>">
            <?php echo htmlspecialchars($post['postado']); ?>
        </a>
    </strong> em
    <time datetime="<?php echo $post['post_data']; ?>"><?php echo $formatter->format(new DateTime($post['post_data'])); ?></time>
</p>