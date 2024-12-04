// assets/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    // Efecto de onda para botones
    const buttons = document.querySelectorAll('button, .button');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            let ripple = document.createElement('span');
            ripple.classList.add('ripple');
            this.appendChild(ripple);
            let x = e.clientX - e.target.offsetLeft;
            let y = e.clientY - e.target.offsetTop;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            setTimeout(() => {
                ripple.remove();
            }, 300);
        });
    });

    // Tooltips para elementos de acción
    const actionElements = document.querySelectorAll('[data-tooltip]');
    actionElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            let tooltip = document.createElement('div');
            tooltip.classList.add('tooltip');
            tooltip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            let rect = this.getBoundingClientRect();
            tooltip.style.top = `${rect.bottom + 5}px`;
            tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;
        });
        element.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });

    // Animación de entrada para elementos de estadísticas
    const statBoxes = document.querySelectorAll('.stat-box');
    statBoxes.forEach((box, index) => {
        setTimeout(() => {
            box.style.opacity = '1';
            box.style.transform = 'translateY(0)';
        }, 100 * index);
    });
});


