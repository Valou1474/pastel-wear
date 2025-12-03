document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('container');

  // Nouveaux IDs pour éviter les conflits
  const registerButton = document.getElementById('switch-register');
  const loginButton = document.getElementById('switch-login');

  if (!container || !registerButton || !loginButton) {
    return;
  }

  registerButton.addEventListener('click', () => {
    container.classList.add('active');
    container.classList.remove('close');
  });

  loginButton.addEventListener('click', () => {
    container.classList.add('close');
    container.classList.remove('active');
  });
});
