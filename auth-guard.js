/**
 * Auth Guard - PHP Session Version
 */
class AuthGuard {
    constructor() {
        this.user = null;
        this.checkSession();
    }

    async checkSession() {
        try {
            const data = await fetch('check_session.php').then(r => r.json());
            if (data.authenticated) {
                this.user = data.user;
                localStorage.setItem('fellahdz_user', JSON.stringify(data.user));
            } else {
                this.user = null;
                localStorage.removeItem('fellahdz_user');
            }
        } catch {
            const saved = localStorage.getItem('fellahdz_user');
            if (saved) this.user = JSON.parse(saved);
        }
    }

    async login(email, password) {
        try {
            const data = await fetch('login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            }).then(r => r.json());
            if (data.success) {
                this.user = data.user;
                localStorage.setItem('fellahdz_user', JSON.stringify(data.user));
                return { success: true, user: data.user, redirect: data.redirect };
            }
            return { success: false, error: data.error || 'Login failed' };
        } catch {
            return { success: false, error: 'Network error' };
        }
    }

    logout() {
        fetch('logout.php').then(() => {
            this.user = null;
            localStorage.removeItem('fellahdz_user');
            window.location.href = 'logiin.html';
        });
    }

    getUser()  { return this.user; }
    isAdmin()  { return this.user?.role === 'admin'; }
    isFarmer() { return this.user?.role === 'farmer'; }
}

const authGuard = new AuthGuard();