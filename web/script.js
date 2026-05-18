function getQueryParam(param) {
    let urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

function showStatus(msg, isError) {
    let div = document.getElementById('statusMsg');
    div.innerHTML = msg;
    div.style.color = isError ? '#ff6b6b' : '#2ecc71';
    setTimeout(() => { div.innerHTML = ''; }, 5000);
}

function loadAuthForm() {
    fetch('captive.php?action=getAuthMethod')
        .then(res => res.json())
        .then(data => {
            let method = data.method;
            let container = document.getElementById('authFields');
            container.innerHTML = '';
            if (method === 'social') {
                container.innerHTML = `
                    <button type="button" id="fbBtn"><i class="fab fa-facebook-f"></i> Facebook</button>
                    <button type="button" id="googleBtn"><i class="fab fa-google"></i> Google</button>
                `;
                document.getElementById('fbBtn').onclick = () => socialLogin('facebook');
                document.getElementById('googleBtn').onclick = () => socialLogin('google');
            } else if (method === 'sms') {
                container.innerHTML = `
                    <input type="tel" id="phone" placeholder="Phone number" required>
                    <input type="text" id="otp" placeholder="OTP (123456)" required>
                `;
            } else if (method === 'click') {
                container.innerHTML = `<label><input type="checkbox" id="acceptTerms" required> I accept the Terms & Conditions</label>`;
            } else if (method === 'voucher') {
                container.innerHTML = `<input type="text" id="voucher" placeholder="Voucher code" required>`;
            }
        });
}

function socialLogin(provider) {
    fetch('captive.php?action=socialLogin&provider=' + provider)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || 'status.php';
            } else {
                showStatus('Login failed', true);
            }
        });
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData();
    let method = document.getElementById('authFields').innerHTML;
    if (method.includes('sms')) {
        let phone = document.getElementById('phone').value;
        let otp = document.getElementById('otp').value;
        formData.append('action', 'smsLogin');
        formData.append('phone', phone);
        formData.append('otp', otp);
    } else if (method.includes('click')) {
        let accept = document.getElementById('acceptTerms').checked;
        if (!accept) { showStatus('You must accept terms', true); return; }
        formData.append('action', 'clickThrough');
    } else if (method.includes('voucher')) {
        let voucher = document.getElementById('voucher').value;
        formData.append('action', 'voucherLogin');
        formData.append('voucher', voucher);
    } else {
        showStatus('Invalid method', true);
        return;
    }
    fetch('captive.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || 'status.php';
            } else {
                showStatus(data.error || 'Login failed', true);
            }
        });
});

function checkAuth() {
    fetch('status.php')
        .then(res => res.json())
        .then(data => {
            if (data.authenticated) {
                document.getElementById('loginForm').style.display = 'none';
                document.getElementById('logoutLink').style.display = 'inline-block';
                let msg = `Connected. Used: ${data.used_mb} MB / ${data.quota_mb} MB, ${data.used_min} min / ${data.quota_min} min`;
                document.getElementById('infoMsg').innerHTML = msg;
                document.getElementById('statusMsg').innerHTML = 'Already authenticated.';
            } else {
                loadAuthForm();
            }
        });
}
checkAuth();