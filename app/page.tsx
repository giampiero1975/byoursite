const services = [
  ['Siti vetrina', 'Portfolio e presenze digitali veloci, curate e facili da aggiornare.'],
  ['Web app leggere', 'Strumenti su misura per automatizzare flussi, preventivi, raccolte dati e dashboard.'],
  ['Identita digitale', 'Logo, microcopy, layout e componenti coerenti per raccontare meglio il progetto.'],
];

const projects = [
  ['Portfolio personale', 'Hero tipografica, sezioni servizi, metodo e contatto diretto.'],
  ['Landing per attivita', 'Pagina orientata alla conversione con messaggio chiaro e call to action visibili.'],
  ['Area operativa', 'Mini dashboard per gestire dati, richieste e stato lavori senza fogli sparsi.'],
];

const method = ['Ascolto', 'Struttura', 'Design', 'Sviluppo', 'Pubblicazione'];

export default function Home() {
  return (
    <main className="min-h-screen overflow-hidden bg-[#030711] text-white selection:bg-cyan-300 selection:text-[#030711]">
      <div className="pointer-events-none fixed inset-0 opacity-80">
        <div className="absolute inset-0 bg-[linear-gradient(rgba(95,214,246,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(95,214,246,0.08)_1px,transparent_1px)] bg-[size:86px_86px]" />
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_68%_28%,rgba(128,88,255,0.22),transparent_26%),radial-gradient(circle_at_20%_70%,rgba(68,205,234,0.16),transparent_24%),linear-gradient(180deg,rgba(3,7,17,0.34),#030711_88%)]" />
      </div>

      <header className="relative z-10 flex min-h-24 items-center justify-between px-5 sm:px-8 lg:px-16">
        <a href="#top" className="flex items-center" aria-label="BYOURSITE home">
          <img src="/logo-byoursite-header.jpg" alt="BYOURSITE" className="h-16 w-56 object-contain object-center sm:w-72" />
        </a>
        <nav className="hidden items-center gap-10 font-mono text-sm uppercase text-white/82 lg:flex">
          <a href="#servizi" className="transition hover:text-cyan-300">Servizi</a>
          <a href="#progetti" className="transition hover:text-cyan-300">Progetti</a>
          <a href="#metodo" className="transition hover:text-cyan-300">Metodo</a>
          <a href="#chi-sono" className="transition hover:text-cyan-300">Chi sono</a>
          <a href="#contatti" className="transition hover:text-cyan-300">Contatti</a>
        </nav>
        <a href="mailto:info@byoursite.it" className="border border-cyan-300/70 px-4 py-3 font-mono text-xs uppercase text-white transition hover:bg-cyan-300 hover:text-[#030711] sm:px-7">
          Parliamone <span aria-hidden="true">↗</span>
        </a>
      </header>

      <section id="top" className="relative z-10 grid min-h-[calc(100vh-6rem)] content-center px-5 pb-12 pt-8 sm:px-8 lg:px-16">
        <div className="grid gap-10 lg:grid-cols-[0.78fr_1.22fr] lg:items-center">
          <div className="order-2 max-w-xl lg:order-1">
            <p className="font-mono text-lg uppercase leading-relaxed text-white/78">
              Il tuo <span className="text-cyan-300">sito</span>.<br />
              Al tuo <span className="text-violet-300">fianco</span>.
            </p>
            <p className="mt-12 max-w-lg font-mono text-xl leading-relaxed text-white sm:text-2xl">
              Progetto e sviluppo siti web e soluzioni digitali <span className="text-cyan-300">su misura</span>, seguendoti dalla prima idea fino alla pubblicazione.
            </p>
            <div className="mt-10 flex flex-col gap-4 sm:flex-row">
              <a href="mailto:info@byoursite.it" className="bg-cyan-300 px-7 py-4 text-center font-mono text-sm uppercase text-[#06101a] shadow-[0_0_36px_rgba(93,213,244,0.34)] transition hover:bg-white">
                Raccontami il tuo progetto <span aria-hidden="true">↗</span>
              </a>
              <a href="#metodo" className="border border-white/55 px-7 py-4 text-center font-mono text-sm uppercase text-white transition hover:border-violet-300 hover:text-violet-200">
                Scopri come lavoro <span aria-hidden="true">↗</span>
              </a>
            </div>
          </div>

          <div className="order-1 lg:order-2">
            <p className="mb-5 text-right font-mono text-xs uppercase tracking-[0.34em] text-white/52">Web solutions</p>
            <h1 className="relative font-sans text-[clamp(3.6rem,9vw,9.5rem)] font-black uppercase leading-[0.9] tracking-normal text-white">
              <span className="block">Siti web</span>
              <span className="-mt-2 block">gestionali</span>
              <span className="ml-[28%] block w-max bg-gradient-to-r from-cyan-300 via-sky-500 to-violet-300 bg-clip-text text-transparent drop-shadow-[0_0_28px_rgba(88,205,238,0.38)]">e API</span>
            </h1>
          </div>
        </div>
      </section>

      <section id="servizi" className="relative z-10 border-y border-white/10 bg-white/[0.035] px-5 py-20 sm:px-8 lg:px-16">
        <div className="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.7fr_1.3fr]">
          <div>
            <p className="font-mono text-sm uppercase text-cyan-300">Servizi</p>
            <h2 className="mt-4 max-w-md text-4xl font-semibold leading-tight sm:text-5xl">Soluzioni chiare per partire, crescere e farti trovare.</h2>
          </div>
          <div className="grid gap-4 md:grid-cols-3">
            {services.map(([title, text]) => (
              <article key={title} className="border border-white/12 bg-[#07101d]/82 p-6">
                <h3 className="text-xl font-semibold">{title}</h3>
                <p className="mt-5 leading-7 text-white/66">{text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section id="progetti" className="relative z-10 px-5 py-20 sm:px-8 lg:px-16">
        <div className="mx-auto max-w-7xl">
          <div className="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
            <div>
              <p className="font-mono text-sm uppercase text-violet-300">Progetti</p>
              <h2 className="mt-4 text-4xl font-semibold sm:text-5xl">Cosa posso costruire con te</h2>
            </div>
            <p className="max-w-md text-white/64">Esempi concreti di lavori adatti a freelance, piccole aziende, creator e professionisti che vogliono una presenza digitale piu forte.</p>
          </div>
          <div className="mt-10 grid gap-4 md:grid-cols-3">
            {projects.map(([title, text], index) => (
              <article key={title} className="min-h-56 border border-white/12 bg-white/[0.045] p-6">
                <span className="font-mono text-sm text-cyan-300">0{index + 1}</span>
                <h3 className="mt-12 text-2xl font-semibold">{title}</h3>
                <p className="mt-4 leading-7 text-white/64">{text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section id="metodo" className="relative z-10 border-y border-white/10 px-5 py-20 sm:px-8 lg:px-16">
        <div className="mx-auto max-w-7xl">
          <p className="font-mono text-sm uppercase text-cyan-300">Metodo</p>
          <div className="mt-8 grid gap-4 md:grid-cols-5">
            {method.map((step, index) => (
              <div key={step} className="border-l border-white/18 py-4 pl-5">
                <span className="font-mono text-sm text-white/42">0{index + 1}</span>
                <p className="mt-6 text-2xl font-semibold">{step}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section id="chi-sono" className="relative z-10 px-5 py-20 sm:px-8 lg:px-16">
        <div className="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1fr_1fr] lg:items-end">
          <h2 className="text-4xl font-semibold leading-tight sm:text-6xl">Un partner tecnico che traduce idee in esperienze digitali.</h2>
          <div className="space-y-6 text-lg leading-8 text-white/68">
            <p>BYOURSITE nasce per accompagnare persone e attivita nella creazione di siti, interfacce e strumenti web semplici da usare e belli da presentare.</p>
            <p>Lavoro con un processo diretto: capiamo obiettivo, contenuti e priorita, poi costruiamo una versione solida da pubblicare e migliorare.</p>
          </div>
        </div>
      </section>

      <footer id="contatti" className="relative z-10 border-t border-white/10 px-5 py-12 sm:px-8 lg:px-16">
        <div className="mx-auto flex max-w-7xl flex-col justify-between gap-8 md:flex-row md:items-center">
          <div>
            <p className="font-mono text-sm uppercase text-cyan-300">Disponibile per nuovi progetti</p>
            <h2 className="mt-3 text-3xl font-semibold">Mettiamo online la tua prossima idea.</h2>
          </div>
          <a href="mailto:info@byoursite.it" className="border border-cyan-300 px-7 py-4 text-center font-mono text-sm uppercase transition hover:bg-cyan-300 hover:text-[#030711]">info@byoursite.it</a>
        </div>
      </footer>
    </main>
  );
}
