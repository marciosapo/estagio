*<div class="container ml-5">
    <?php if (isset($post) && !empty($post)): ?>
        <div class="row">
                <div class="col-12 mb-4">
                    <div class="card border-dark custom-shadow">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $post['title']; ?></h5>
                            <p class="card-text"><?php echo $post['post']; ?></p>
                            <div class="d-flex justify-content-end align-items-center gap-2 mt-3 mb-3 mr-3">
                        <p class="text-muted mb-0">
                                Postado por <strong><?php echo $post['postado']; ?></strong> 
                                em <time datetime="<?php echo $post['post_data']; ?>"><?php echo date('d M Y', strtotime($post['post_data'])); ?></time>
                            </p>
                        </div>
                            <?php if(!empty($post['comentarios'])): ?> 
                    <div id="comment-section-<?php echo $post['id']; ?>" class="mt-4">
                        <h5>Comentários:</h5>
                        <?php foreach ($post['comentarios'] as $comentario): ?>
                            <div class="comment mb-3">
                                <strong><?php echo $comentario['autor'] . "(" . $comentario['post_data'] . ")"; ?></strong>: 
                                <p class="text-muted"><?php echo $comentario['comentario']; ?></p>
                                <?php if(isset($_SESSION['user'])): ?>
                                    <?php if($_SESSION['user'] == $comentario['autor'] && isset($_POST['editar'])): ?>
                                        <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                                            <input type=hidden name=id value="<?php echo $post['id']; ?>">
                                            <input type=hidden name=id_parent value="<?php echo $comentario['id']; ?>">         
                                            <input type="hidden" name="editarid" value="<?php echo $comentario['id']; ?>">
                                            <div class="form-group">
                                                <textarea id="comment" value="<?php echo $comentario['post'] ?>" name="comment" class="form-control no-resize" rows="3" cols="80" placeholder="Escreva a sua resposta aqui..."></textarea>
                                            </div>
                                            <input type=submit name=cancelar value="Cancelar" class="btn btn-secondary">
                                            <input type=submit name=editar value="Editar" class="btn btn-secondary">
                                        </form>
                                    <?php elseif($_SESSION['user'] == $comentario['autor'] && !isset($_POST['editar'])): ?>
                                        <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                                        <input type=hidden name=id value="<?php echo $comentario['id']; ?>">
                                        <input type="hidden" name="editarid" value="<?php echo $resposta['id']; ?>">
                                        <input type=submit name=editar value="Editar" class="btn btn-sm btn-secondary">
                                        <input type=submit name=apagar value="Apagar" class="btn btn-sm btn-secondary">
                                    </form>
                                    <?php endif; ?> 
                                    <?php if (isset($_POST['responder']) && $_POST['respondeid'] == $comentario['id']): ?>
                                        <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                                            <input type=hidden name=id value="<?php echo $post['id']; ?>">
                                            <input type=hidden name=id_parent value="<?php echo $comentario['id']; ?>">         
                                            <input type="hidden" name="respondeid" value="<?php echo $comentario['id']; ?>">
                                            <div class="form-group">
                                                <textarea id="comment" name="comment" class="form-control no-resize" rows="3" cols="80" placeholder="Escreva a sua resposta aqui..."></textarea>
                                            </div>
                                            <input type="submit" class="btn btn-secondary" name="cancelar" value="Cancelar">
                                            <input type="submit" class="btn btn-secondary" name="resposta" value="Enviar Resposta">
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    if(isset($_SESSION['user']) && $_SESSION['user'] != $comentario['autor'] && !isset($_POST['responder'])): ?>
                                        <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                            <input type="hidden" name="respondeid" value="<?php echo $comentario['id']; ?>">
                                            <div class="form-group">
                                                <input type="submit" name="responder" value="Responder" class="btn btn-sm btn-secondary">
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (!empty($comentario['respostas'])): ?>
                                    <div class="respostas ml-4">
                                        <?php foreach ($comentario['respostas'] as $resposta): ?>
                                            <div class="resposta mb-3">
                                                <strong><?php echo $resposta['autor'] . "(" . $comentario['post_data'] . ")"; ?></strong>: 
                                                <p class="text-muted"><?php echo $resposta['comentario']; ?></p>
                                            </div>
                                            <?php if(isset($_SESSION['user'])): ?>
                                                <?php if (isset($_POST['response']) && $_POST['responseid'] == $comentario['id']): ?>
                                                <form action="/Blog/verPost" method="POST" class="ml-3 mb-3 mr-3">
                                                    <input type=hidden name=id value="<?php echo $post['id']; ?>">
                                                    <input type="hidden" name="respondeid" value="<?php echo $comentario['id']; ?>">
                                                    <div class="form-group">
                                                        <textarea id="comment" value="<?php echo $comentario['post'] ?>" name="comment" class="form-control no-resize" rows="3" cols="80" placeholder="Escreva a sua resposta aqui..."></textarea>
                                                    </div>
                                                    <input type="submit" class="btn btn-secondary" name="cancelar" value="Cancelar">
                                                    <input type="submit" class="btn btn-secondary" name="comentar" value="Enviar Comentário">
                                                </form>


                                            <?php endif; ?>
                                            <div class="d-flex gap-2 ml-1 mb-3">
                                            <?php if (isset($_POST['editar']) && $_POST['editarid'] == $resposta['id']): ?>
                                                    <form action="/Blog/verPost" method="POST" class="ml-3 mb-3 mr-3">
                                                        <input type=hidden name=id value="<?php echo $post['id']; ?>">
                                                        <input type=hidden name=id_parent value="<?php echo $comentario['id']; ?>">
                                                        <input type="hidden" name="editarid" value="<?php echo $resposta['id']; ?>">
                                                        <div class="form-group">
                                                            <textarea id="comment" name="comment" class="form-control no-resize" rows="3" cols="80" placeholder="Escreva a sua resposta aqui..."><?php echo $resposta['comentario'] ?></textarea>
                                                        </div>
                                                        <input type=submit name=cancelar value="Cancelar" class="btn btn-secondary">
                                                        <input type=submit name=editar value="Gravar" class="btn btn-secondary">
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (isset($_POST['responder']) && $_POST['respondeid'] == $resposta['id']): ?>
                                                    <form action="/Blog/verPost" method="POST" class="ml-3 mb-3 mr-3">
                                                        <input type=hidden name=id value="<?php echo $post['id']; ?>">
                                                        <input type=hidden name=id_parent value="<?php echo $comentario['id']; ?>">
                                                        <input type="hidden" name="respondeid" value="<?php echo $comentario['id']; ?>">
                                                        <div class="form-group">
                                                            <textarea id="comment" name="comment" class="form-control no-resize" rows="3" cols="80" placeholder="Escreva a sua resposta aqui..."></textarea>
                                                        </div>
                                                        <input type="submit" class="btn btn-secondary" name="cancelar" value="Cancelar">
                                                        <input type="submit" class="btn btn-secondary" name="resposta" value="Enviar Resposta">
                                                    </form>
                                                    
                                                    <?php else: ?>
                                                        <?php if($_SESSION['user'] == $resposta['autor'] && !isset($_POST['editar'])): ?>
                                                        <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                                                        <input type=hidden name=id value="<?php echo $post['id']; ?>">
                                                        <input type=hidden name=id_parent value="<?php echo $comentario['id']; ?>">
                                                        <input type="hidden" name="editarid" value="<?php echo $resposta['id']; ?>">
                                                        <input type=submit name=editar value="Editar" class="btn btn-sm btn-secondary">
                                                        <input type=submit name=apagar value="Apagar" class="btn btn-sm btn-secondary">
                                                    </form>
                                                    <?php else: ?> 
                                                    <form action="/Blog/verPost" method="POST" class="ml-3 mb-3">
                                                        <input type=hidden name=id value="<?php echo $post['id']; ?>">
                                                        <input type=hidden name=id_parent value="<?php echo $comentario['id']; ?>">
                                                        <input type="hidden" name="respondeid" value="<?php echo $resposta['id']; ?>">
                                                        <div class="form-group">
                                                            <input type=submit name=responder value="Responder" class="btn btn-sm btn-secondary">
                                                        </div>
                                                    </form>    
                                                    <?php endif; ?>
                                                    
                                                <?php endif; ?>
                                            <?php endif; ?>
                                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                        </div>
                        <?php if(isset($_SESSION['user']) && $_SESSION['user'] != $post['postado']): ?>
                            <form action="/Blog/verPost" method="POST" class="ml-3 mb-3 mr-3">
                                <input type=hidden name=id value="<?php echo $post['id']; ?>">
                                <div class="form-group">
                                    <label for="comment">Adicionar um comentário</label>
                                    <textarea id="comment" name="comment" class="form-control" rows="3" placeholder="Escreva seu comentário aqui..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-secondary">Enviar Comentário</button>
                            </form>
                        <?php endif; ?>
                    </div>    
                </div>
        </div>
<?php else: ?>
    <div class="container py-5">
        <p class="text-center">Nenhum post encontrado.</p>
    </div>
<?php endif; ?>
</div>