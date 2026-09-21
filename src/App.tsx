import {FormEvent, useState} from 'react';
import {
  ArrowRight,
  BarChart3,
  Bell,
  CheckCircle2,
  ChevronRight,
  CircleDollarSign,
  Clock3,
  ExternalLink,
  Gift,
  LayoutDashboard,
  LogOut,
  Menu,
  MessageCircle,
  ShieldCheck,
  Sparkles,
  TrendingUp,
  Users,
  X,
} from 'lucide-react';

const telegramUrl = 'https://t.me/CapitalNestSupport';

function AdLabel() {
  return <span className="ad-label">ADVERTISEMENT</span>;
}

function AdCreative({compact = false}: {compact?: boolean}) {
  return (
    <a className={`ad-space ${compact ? 'ad-space-compact' : ''}`} href={telegramUrl} target="_blank" rel="noreferrer">
      <div className="ad-space-icon"><Sparkles size={compact ? 18 : 24} /></div>
      <div className="ad-space-copy">
        <strong>Place your ads here</strong>
        <span>{compact ? 'Reach our audience with your brand.' : 'Premium ad space available for your brand.'}</span>
        <b>Contact on Telegram <em>@CapitalNestSupport</em> <ArrowRight size={14} /></b>
      </div>
      <div className="ad-space-badge">AVAILABLE</div>
    </a>
  );
}

function Login({onLogin}: {onLogin: () => void}) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const submit = (event: FormEvent) => {
    event.preventDefault();
    onLogin();
  };

  return (
    <main className="auth-shell">
      <div className="auth-decoration auth-decoration-left" />
      <div className="auth-decoration auth-decoration-right" />
      <section className="auth-content">
        <div className="brand-lockup">
          <div className="brand-mark"><CircleDollarSign size={22} /></div>
          <span>Capital<span>Nest</span></span>
        </div>
        <div className="auth-grid">
          <div className="auth-intro">
            <span className="eyebrow"><ShieldCheck size={15} /> SECURE MEMBER ACCESS</span>
            <h1>Grow with a smarter<br /><em>financial future.</em></h1>
            <p>Manage your portfolio, discover new opportunities, and stay ahead with one simple dashboard.</p>
            <div className="trust-row">
              <div className="avatar-stack"><span>R</span><span>S</span><span>A</span><span>+</span></div>
              <div><strong>10,000+</strong><small>members trust CapitalNest</small></div>
            </div>
          </div>
          <div className="auth-panel">
            <div className="panel-heading"><div><h2>Welcome back</h2><p>Sign in to continue to your account</p></div><div className="online-dot" /></div>
            <form onSubmit={submit}>
              <label>Email address<input type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="you@example.com" required /></label>
              <label>Password<div className="password-field"><input type="password" value={password} onChange={(e) => setPassword(e.target.value)} placeholder="Enter your password" required /><button type="button" className="show-password" aria-label="Show password">Show</button></div></label>
              <div className="form-options"><label className="check-label"><input type="checkbox" /> Remember me</label><a href={telegramUrl} target="_blank" rel="noreferrer">Forgot password?</a></div>
              <button className="primary-button" type="submit">Sign in <ArrowRight size={17} /></button>
            </form>
            <p className="auth-footer">New to CapitalNest? <a href={telegramUrl} target="_blank" rel="noreferrer">Create an account</a></p>
          </div>
        </div>
        <div className="login-ad"><AdLabel /><AdCreative compact /></div>
      </section>
      <footer className="auth-bottom"><span>© 2025 CapitalNest Nepal</span><span>Trusted · Secure · Transparent</span></footer>
    </main>
  );
}

function Dashboard({onLogout}: {onLogout: () => void}) {
  const [showPopup, setShowPopup] = useState(true);
  const [menuOpen, setMenuOpen] = useState(false);
  return (
    <div className="dashboard-shell">
      <aside className={`sidebar ${menuOpen ? 'sidebar-open' : ''}`}>
        <div className="brand-lockup"><div className="brand-mark"><CircleDollarSign size={22} /></div><span>Capital<span>Nest</span></span></div>
        <nav><a className="active" href="#overview"><LayoutDashboard size={18} /> Overview</a><a href="#portfolio"><BarChart3 size={18} /> My portfolio</a><a href="#community"><Users size={18} /> Community</a></nav>
        <div className="sidebar-help"><MessageCircle size={18} /><strong>Need help?</strong><span>Our team is online</span><a href={telegramUrl} target="_blank" rel="noreferrer">Chat on Telegram <ExternalLink size={13} /></a></div>
        <button className="logout-button" onClick={onLogout}><LogOut size={17} /> Sign out</button>
      </aside>
      <main className="dashboard-main">
        <header className="dashboard-header"><button className="mobile-menu" onClick={() => setMenuOpen(!menuOpen)} aria-label="Toggle menu"><Menu /></button><div><span className="header-kicker">MONDAY, SEPTEMBER 22, 2025</span><h1>Good morning, Alex <span>✦</span></h1></div><div className="header-actions"><button className="icon-button" aria-label="Notifications"><Bell size={19} /><i /></button><div className="profile"><div className="profile-avatar">A</div><div><strong>Alex Morgan</strong><small>Premium member</small></div><ChevronRight size={15} /></div></div></header>
        <div className="dashboard-scroll">
          <AdLabel /><AdCreative />
          <section className="section-heading"><div><span className="eyebrow">YOUR OVERVIEW</span><h2>Everything in one place</h2></div><button className="date-button"><Clock3 size={15} /> This month <ChevronRight size={14} /></button></section>
          <div className="stat-grid"><div className="stat-card"><div className="stat-icon purple"><CircleDollarSign size={19} /></div><span>Total balance</span><strong>$24,680.40</strong><small className="positive">+12.8% <span>vs last month</span></small></div><div className="stat-card"><div className="stat-icon green"><TrendingUp size={19} /></div><span>Total returns</span><strong>$3,248.90</strong><small className="positive">+8.4% <span>this month</span></small></div><div className="stat-card"><div className="stat-icon orange"><BarChart3 size={19} /></div><span>Active plans</span><strong>04</strong><small>2 mature this month</small></div></div>
          <div className="content-grid"><section className="chart-card"><div className="card-heading"><div><span className="eyebrow">PORTFOLIO GROWTH</span><h3>Performance</h3></div><span className="chart-value">+24.6%</span></div><div className="chart-area"><div className="chart-lines"><i /><i /><i /><i /></div><svg viewBox="0 0 600 180" preserveAspectRatio="none"><defs><linearGradient id="fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stopColor="#7767f7" stopOpacity=".3" /><stop offset="100%" stopColor="#7767f7" stopOpacity="0" /></linearGradient></defs><path d="M0 155 C55 140 65 148 105 120 S155 130 190 106 S245 100 275 114 S330 70 365 85 S410 78 445 50 S485 65 520 27 S555 35 600 8 V180 H0Z" fill="url(#fill)" /><path d="M0 155 C55 140 65 148 105 120 S155 130 190 106 S245 100 275 114 S330 70 365 85 S410 78 445 50 S485 65 520 27 S555 35 600 8" fill="none" stroke="#7767f7" strokeWidth="3" /></svg></div><div className="chart-labels"><span>May</span><span>Jun</span><span>Jul</span><span>Aug</span><span>Sep</span></div></section><section className="activity-card"><div className="card-heading"><div><span className="eyebrow">RECENT ACTIVITY</span><h3>Latest updates</h3></div><a href="#activity">View all</a></div><div className="activity-item"><div className="activity-icon green"><CheckCircle2 size={17} /></div><div><strong>Monthly return credited</strong><small>Investment plan · Today</small></div><b>+$482.60</b></div><div className="activity-item"><div className="activity-icon purple"><CircleDollarSign size={17} /></div><div><strong>Deposit received</strong><small>Wallet · 2 days ago</small></div><b>+$2,000.00</b></div><div className="activity-item"><div className="activity-icon orange"><Clock3 size={17} /></div><div><strong>Plan renewal scheduled</strong><small>Growth Plus · 5 days</small></div><b className="muted">Pending</b></div></section></div>
          <div className="bottom-ad"><AdLabel /><AdCreative compact /></div>
        </div>
      </main>
      {showPopup && <div className="modal-backdrop" role="dialog" aria-modal="true"><div className="promo-modal"><button className="modal-close" onClick={() => setShowPopup(false)} aria-label="Close promotion"><X size={18} /></button><div className="modal-spark"><Gift size={26} /></div><span className="eyebrow">A QUICK HELLO</span><h2>Have a brand to share?</h2><p>Reach thousands of active members with a premium ad placement on CapitalNest.</p><a className="primary-button modal-button" href={telegramUrl} target="_blank" rel="noreferrer">Place your ad <ArrowRight size={17} /></a><small>Message us directly on Telegram <strong>@CapitalNestSupport</strong></small></div></div>}
    </div>
  );
}

export default function App() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  return isLoggedIn ? <Dashboard onLogout={() => setIsLoggedIn(false)} /> : <Login onLogin={() => setIsLoggedIn(true)} />;
}
