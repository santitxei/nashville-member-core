document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Lógica Tap-to-Verify para la tarjeta digital
    const memberCard = document.querySelector('.member-card__pulse');
    if (memberCard) {
        memberCard.addEventListener('click', async () => {
            const statusEl = document.querySelector('.verify-status');
            statusEl.textContent = 'Verificando...';
            
            try {
                const res = await fetch(`${nashvilleData.apiUrl}/verify-card`, {
                    method: 'POST',
                    headers: { 
                        'X-WP-Nonce': nashvilleData.nonce,
                        'Content-Type': 'application/json'
                    }
                });
                const data = await res.json();
                
                if (data.verified) {
                    statusEl.innerHTML = `&#10003; Activa &middot; ${data.timestamp}`;
                } else {
                    statusEl.textContent = 'Error de verificación';
                }
            } catch (error) {
                statusEl.textContent = 'Error de conexión';
            }
        });
    }

});
