<nav>
    <ul class="pagination">
        <?php if ($paginaAtual > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?pagina=<?php echo $paginaAtual - 1; ?>">Anterior</a>
            </li>
        <?php else: ?>
            <li class="page-item disabled"><span class="page-link">Anterior</span></li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="page-item <?php echo ($i == $paginaAtual) ? 'active' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($paginaAtual < $totalPaginas): ?>
            <li class="page-item">
                <a class="page-link" href="?pagina=<?php echo $paginaAtual + 1; ?>">Próxima</a>
            </li>
        <?php else: ?>
            <li class="page-item disabled"><span class="page-link">Próxima</span></li>
        <?php endif; ?>
    </ul>
</nav>