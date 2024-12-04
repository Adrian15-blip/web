<?php
// includes/footer.php
?>
    </div> <!-- Cierre del div content -->
    <footer class="footer">
        <style>
            .footer {
                background-color: var(--primary-color);
                color: var(--text-color);
                padding: 2rem 0;
                margin-top: 3rem;
            }

            .footer-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 1rem;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 2rem;
            }

            .footer-section h3 {
                color: var(--accent-color);
                margin-bottom: 1rem;
                font-size: 1.2rem;
            }

            .footer-section p,
            .footer-section a {
                color: var(--text-color);
                text-decoration: none;
                margin: 0.5rem 0;
                display: block;
            }

            .footer-section a:hover {
                color: var(--accent-color);
            }

            .social-links {
                display: flex;
                gap: 1rem;
                margin-top: 1rem;
            }

            .social-links a {
                color: var(--text-color);
                font-size: 1.5rem;
                transition: color 0.3s;
            }

            .social-links a:hover {
                color: var(--accent-color);
            }

            .footer-bottom {
                text-align: center;
                margin-top: 2rem;
                padding-top: 2rem;
                border-top: 1px solid rgba(255,255,255,0.1);
            }

            @media (max-width: 768px) {
                .footer-container {
                    grid-template-columns: 1fr;
                    text-align: center;
                }

                .social-links {
                    justify-content: center;
                }
            }
        </style>

        <div class="footer-container">
            <div class="footer-section">
                <h3>Sobre Nosotros</h3>
                <p>Somos una empresa dedicada a brindar soluciones profesionales en servicios de ingeniería y mantenimiento industrial.</p>
            </div>

            <div class="footer-section">
                <h3>Enlaces Rápidos</h3>
                <a href="<?php echo $basePath; ?>about.php">Acerca de</a>
                <a href="<?php echo $basePath; ?>services.php">Servicios</a>
                <a href="<?php echo $basePath; ?>contact.php">Contacto</a>
            </div>

            <div class="footer-section">
                <h3>Contacto</h3>
                <p><i class="fas fa-phone"></i> +51 123 456 789</p>
                <p><i class="fas fa-envelope"></i> info@empresa.com</p>
                <p><i class="fas fa-map-marker-alt"></i> Lima, Perú</p>
            </div>

            <div class="footer-section">
                <h3>Síguenos</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Cotizaciones. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/main.js"></script>
</body>
</html>