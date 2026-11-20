const form = document.getElementById('loginForm');
const error = document.getElementById('loginError');

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  error.textContent = '';
  const button = form.querySelector('button');
  button.disabled = true;
  button.textContent = 'Ingresando...';
  try {
    const response = await fetch('login.php', {
      method: 'POST',
      body: new FormData(form),
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'No se pudo iniciar sesión');
    window.location.href = 'app.php';
  } catch (err) {
    error.textContent = err.message;
  } finally {
    button.disabled = false;
    button.textContent = 'Iniciar sesión';
  }
});
