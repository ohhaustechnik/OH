import { useEffect, useState } from 'react'
import { motion } from 'framer-motion'
import { api, eur } from '../api.js'
import KpiCard from '../components/KpiCard.jsx'
import Progress from '../components/Progress.jsx'
const AG={mert:'🧠',dilara:'🚀',kaan:'💬',emre:'🧮',aylin:'💰',yusuf:'🏗️',baran:'👥',system:'•'}
export default function Dashboard({ data }) {
  const [d,setD]=useState(data||null)
  useEffect(()=>{ if(!d) api('dashboard').then(setD).catch(()=>{}) },[])
  if(!d) return <div className="text-muted p-6">Lade …</div>
  const stats=d.stats||{}, offen=d.offen||[], erled=d.erledigt||[], akt=d.aktivitaet||[]
  const won=(d.leads||[]).filter(l=>['gewonnen','abgeschlossen'].includes(l.status)).length
  const umsatz=won*2000 // grobe Schätzung bis echte Umsatzdaten angebunden sind
  const rot=offen.filter(t=>t.prio==='rot').length
  return (
    <div className="p-4 md:p-6 max-w-5xl mx-auto">
      <motion.div initial={{opacity:0,y:-8}} animate={{opacity:1,y:0}}>
        <h1 className="text-2xl font-extrabold text-white">Guten Tag, Chef.</h1>
        <p className="text-muted text-sm">Dein Unternehmen auf einen Blick · Ziel: 1 Mio € in 5 Monaten</p>
      </motion.div>

      <div className="mt-5"><Progress value={umsatz} goal={1000000}/></div>

      <div className="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
        <KpiCard title="Offene Aufgaben" value={offen.length} status={rot? 'bad':'ok'} sub={rot? rot+' kritisch':'alles im Griff'} icon="📋" delay={.05}/>
        <KpiCard title="Heiße Anfragen" value={stats.hot||0} status={(stats.hot||0)>0?'warn':'neutral'} icon="🔥" delay={.1}/>
        <KpiCard title="Leads gesamt" value={stats.leads||0} icon="📥" delay={.15}/>
        <KpiCard title="Erledigt" value={erled.length} status="ok" icon="✅" delay={.2}/>
      </div>

      {d.mert && d.mert.text && (
        <motion.div initial={{opacity:0,y:16}} animate={{opacity:1,y:0}} transition={{delay:.25}}
          className="glass glow rounded-2xl p-5 mt-4">
          <div className="flex items-center gap-2 mb-2"><span className="text-xl">🧠</span>
            <b className="text-white">Mert Aldemir</b><span className="text-[10px] text-accent">Geschäftsführer</span></div>
          <div className="text-sm text-txt whitespace-pre-wrap leading-relaxed">{d.mert.text}</div>
        </motion.div>
      )}

      <div className="grid md:grid-cols-2 gap-4 mt-4">
        <motion.div initial={{opacity:0,y:16}} animate={{opacity:1,y:0}} transition={{delay:.3}} className="glass rounded-2xl p-4">
          <div className="text-sm font-bold text-white mb-3">🎯 Wichtigste Aufgaben</div>
          {offen.slice(0,5).map((t,i)=>(
            <div key={i} className="flex items-center gap-3 py-2 border-b border-line last:border-0">
              <span className="w-2 h-2 rounded-full" style={{background: t.prio==='rot'?'#ff5d6c':t.prio==='gelb'?'#e7b14b':'#34e09a'}}/>
              <div className="flex-1 min-w-0"><div className="text-sm text-white truncate">{t.titel}</div>
                <div className="text-[11px] text-muted">{t.bereich} · {t.nutzen}</div></div>
            </div>
          ))}
          {!offen.length && <div className="text-muted text-sm">Nichts offen 🎯</div>}
        </motion.div>

        <motion.div initial={{opacity:0,y:16}} animate={{opacity:1,y:0}} transition={{delay:.35}} className="glass rounded-2xl p-4">
          <div className="text-sm font-bold text-white mb-3">📋 Was die Agenten erledigt haben</div>
          {akt.slice(0,6).map((a,i)=>(
            <div key={i} className="flex items-start gap-3 py-2 border-b border-line last:border-0">
              <span>{AG[a.agent]||'•'}</span>
              <div className="text-[13px] text-txt leading-snug">{a.text}</div>
            </div>
          ))}
          {!akt.length && <div className="text-muted text-sm">Noch keine Aktivität.</div>}
        </motion.div>
      </div>
    </div>
  )
}
