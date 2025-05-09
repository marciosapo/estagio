<?php
function refresh_Pagina($id){
    if(!isset($id)) return;
    echo '
        <form id="redirectForm" action="/Blog/verPost" method="POST">
            <input type="hidden" name="id" value="' . $id . '" />
        </form>
        <script type="text/javascript">
            document.getElementById("redirectForm").submit();
        </script>';
} 
?>