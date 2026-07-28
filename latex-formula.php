<?php
date_default_timezone_set('UTC');
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CNGN LaTeX Formula Reference</title>
    <style>
        :root {
            --bg: #07111d;
            --panel: #101e2d;
            --panel-2: #15283c;
            --text: #e7eef8;
            --muted: #9eb0c3;
            --line: rgba(255,255,255,.08);
            --accent: #7ff1b8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(127,241,184,.10), transparent 24rem),
                linear-gradient(180deg, #07111d 0%, #0a1624 100%);
            color: var(--text);
        }
        .page {
            max-width: 1080px;
            margin: 0 auto;
            padding: 32px 20px 48px;
        }
        .topbar {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }
        .eyebrow {
            margin: 0;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: .16em;
            font-size: .72rem;
        }
        h1 {
            margin: 6px 0 8px;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            line-height: 1.1;
        }
        .intro {
            margin: 0;
            color: var(--muted);
            max-width: 70ch;
            line-height: 1.6;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,.06);
            border: 1px solid var(--line);
            color: var(--text);
            text-decoration: none;
        }
        .back-link:hover {
            background: rgba(255,255,255,.10);
        }
        .panel {
            margin-top: 22px;
            background: rgba(16,30,45,.92);
            border: 1px solid var(--line);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,.28);
        }
        .panel-head {
            padding: 18px 20px;
            border-bottom: 1px solid var(--line);
            background: rgba(255,255,255,.03);
        }
        .panel-head h2 {
            margin: 0 0 6px;
            font-size: 1rem;
        }
        .panel-head p {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
        }
        pre {
            margin: 0;
            padding: 20px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-word;
            color: #dfe8f4;
            background: rgba(7,17,31,.72);
            line-height: 1.55;
            font-size: .95rem;
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="topbar">
            <div>
                <p class="eyebrow">CNGN reference</p>
                <h1>LaTeX formula page</h1>
                <p class="intro">This page holds the standalone LaTeX review source for the current CNGN model notes, without making the main trading page heavier to navigate.</p>
            </div>
            <a class="back-link" href="./index.php">← Back to dashboard</a>
        </div>

        <section class="panel" aria-labelledby="latex-source-title">
            <div class="panel-head">
                <h2 id="latex-source-title">Complete LaTeX review source</h2>
                <p>Copied from the model reference so it can live on its own page.</p>
            </div>
            <pre>\[
\mathbf q_n=(s_n,u_n,v_n,T_n)
\]
\[
\mathbf q_n=
\bigl(1+n\Delta s,\;C_{n-1},\;C_{n-1},\;T_n\bigr),
\qquad \Delta s=\texttt{\$day\_cnt},
\qquad n\ge 1
\]
\[
\tau_x=
300\left\lfloor\frac{t_{\mathrm{now}}}{300}\right\rfloor+300(x+1),
\qquad x=0,\ldots,48
\]
\[
\mathbf q_x^{+}=(s_x+\Delta s,\;u_x,\;L_x,\;\tau_x)
\]
\[
d(\mathbf q)=\left|\operatorname{trunc}(v)-\operatorname{trunc}(u)\right|
\]
\[
G(\mathbf q)=\frac{s}{2}+\frac{3}{2}d(\mathbf q)-1
\]
\[
I(a,b,c)=\frac{(a+b+c)^2}{3}
\]
\[
J(\mathbf q)=I\bigl(s,G(\mathbf q),G(\mathbf q)\bigr)
=\frac{\bigl(s+2G(\mathbf q)\bigr)^2}{3}
\]
\[
W(\mathbf q)=\sqrt{3J(\mathbf q)},
\qquad
S(\mathbf q)=W(\mathbf q)-2G(\mathbf q)
\]
\[
D(\mathbf q)=\frac{S(\mathbf q)}{G(\mathbf q)+1}
\]
\[
W(\mathbf q)=s+2G(\mathbf q)\Longrightarrow
S(\mathbf q)=s\Longrightarrow
D(\mathbf q)=\frac{s}{G(\mathbf q)+1}
=\frac{2s}{s+3d(\mathbf q)}
\]
\[
u=v\Longrightarrow
G(\mathbf q)=\frac{s}{2}-1,
\qquad
D(\mathbf q)=\frac{s}{s/2}=2
\]
\[
P(\mathbf q)=\frac{G(\mathbf q)}{W(\mathbf q)},
\qquad
\beta(\mathbf q)=P(\mathbf q)-\frac14
\]
\[
u=v\Longrightarrow
P(\mathbf q)=\frac{s-2}{4s-4},
\qquad
\beta(\mathbf q)=-\frac{1}{4s-4}
\]
\[
e(\mathbf w)=\left|\operatorname{trunc}(c)-\operatorname{trunc}(b)\right|,
\qquad
H(\mathbf w)=\frac{r}{a/r+(3/2)e(\mathbf w)}
\]
\[
\mathbf w_x=(s_x,u_x,v_x,G(\mathbf q_x))
\]
\[
u=v\Longrightarrow
H(\mathbf w_x)=\frac{G(\mathbf q_x)^2}{s_x}
\]
\[
\lambda(\mathbf q)=
\frac{H(\mathbf w)^2}{2G(\mathbf q)D(\mathbf q)},
\qquad
\widehat{\lambda}(\mathbf q)=1.01^k\lambda(\mathbf q)
\]
\[
M(\mathbf q)=
\widehat{\lambda}(\mathbf q)\frac{\operatorname{trunc}(v)}{10}
-\operatorname{trunc}(G(\mathbf q))
\]
\[
L(\mathbf q)=
B+2\operatorname{round}\left(\frac{M(\mathbf q)}{O},2\right)-E
\]
\[
(\ell_\star,r_\star,L_\star,\beta_\star)
=(\texttt{\$bool1},\texttt{\$bool2},\texttt{\$short\_low},\texttt{\$wall\_bias})
\]
\[
\ell_x=
\begin{cases}
-,&L_x<L_{\mathrm{prev}},\\
+,&\text{otherwise},
\end{cases}
\qquad
r_x^{(0)}=
\begin{cases}
-,&\beta_x<\beta_{\mathrm{prev}},\\
+,&\text{otherwise}.
\end{cases}
\]
\[
r_x=
\begin{cases}
-\,r_x^{(0)},&\lfloor t_{\mathrm{now}}/300\rfloor\bmod2=1,\\
r_x^{(0)},&\text{otherwise},
\end{cases}
\qquad
p_x=\ell_x r_x
\]
\[
g_x=
\begin{cases}
\%,&(\ell_x,r_x)=(-,-),\\
\ell_x,&\text{otherwise}
\end{cases}
\]
\[
\mathrm{score}_x=\mathbf 1[r_x=\ell_x]
\]
\[
A(p)=
\begin{cases}
\mathrm{BUY},&p\in\{--,-+,+-\},\\
\mathrm{SELL},&p=++,\\
\mathrm{NO\ TRADE},&p=\%.
\end{cases}
\]
\[
F(T)=
\begin{cases}
p_T,&T\notin\operatorname{dom}(F),\\
F(T),&T\in\operatorname{dom}(F).
\end{cases}
\]
\[
\mathrm{RIGHT}(T)=
\mathbf 1\!\left[
A(F(T))=\operatorname{direction}_{\mathrm{observed}}(T)
\right]
\]</pre>
        </section>
    </main>
</body>
</html>
