<?php 
    $titulo = "Mensajes - EstateArch"; 
    include 'includes/head.php'; 
?>
<body class="bg-light">

    <?php include 'includes/header.php'; ?>

    <main class="container page-content" style="padding-top: 20px; max-width: 1200px; margin: 0 auto; min-height: 70vh;">
        
        <div class="chat-layout">
            
            <div id="chat-lista" class="chat-lista">
                </div>

            <div id="chat-conversacion" class="chat-conversacion">
                <div class="chat-vacio">Selecciona una conversación para empezar a chatear.</div>
            </div>

        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/navbar.js"></script>
    <script>
        // Llama a la función que dibuja el menú superior
        renderNavbar(''); 
    </script>

    <script src="js/chat.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Chat !== 'undefined') {
                Chat.init();
            } else {
                console.error("El módulo Chat no se cargó correctamente.");
            }
        });
    </script>
</body>
</html>