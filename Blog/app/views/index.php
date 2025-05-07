<div class="container ml-5">
    <h1>Posts do Blog</h1>
    <?php if (isset($posts) && !empty($posts)): ?>
        <div class="row">
            <?php foreach ($posts as $post): ?>
                <form method="POST" action="/Blog/verPost" class="col-12 mb-4">
                    <input type=hidden name="id" value="<?php echo $post['id']; ?>">
                <div class="col-12 mb-4">
                    <div class="card border-dark custom-shadow">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $post['title']; ?></h5>
                            <p class="card-text"><?php echo $post['post']; ?></p>
                           
                        </div>
                        <div class="d-flex justify-content-end align-items-center gap-2 mt-3 mb-3 mr-3">
                        <p class="text-muted mb-0">
                            Postado por <strong><?php echo $post['postado']; ?></strong> 
                            em <time datetime="<?php echo $post['post_data']; ?>">
                                <?php echo date('d M Y', strtotime($post['post_data'])); ?>
                            </time>
                            /
                            <input type=submit class="btn btn-sm btn-outline-secondary"
                                value="Comentários (<?php echo $post['nComentarios']; ?>)">
                        </p>
                        </div>
                    </div>    
                </div>
                                        </form>
            <?php endforeach; ?>
        </div>
<?php else: ?>
    <div class="container py-5">
        <p class="text-center">Nenhum post encontrado.</p>
    </div>
<?php endif; ?>
</div>