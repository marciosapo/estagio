<p class="text-muted mt-4">
    Postado por <strong><?php echo htmlspecialchars($post['postado']); ?></strong> em
    <time datetime="<?php echo $post['post_data']; ?>"><?php echo $formatter->format(new DateTime($post['post_data'])); ?></time>
</p>