<div class="w-25">
    <?php 
        if(isset($post['imagem']) && !empty($post['imagem'])) {
            $postImg = $post['imagem'];
        }else{
            $postImg = "/imgs/post.png";
        } 
        ?>
    <img src="<?php echo $postImg; ?>" 
            alt="Imagem do post" 
            class="img-fluid object-fit-cover rounded" 
            style="height: 250px;">
</div>