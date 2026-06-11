import { motion } from 'framer-motion'
import AnimatedNumber from './AnimatedNumber.jsx'
import { eur } from '../api.js'
export default function Progress({ value=0, goal=1000000 }) {
  const pct = Math.min(100, Math.round((value/goal)*100))
  return (
    <motion.div initial={{opacity:0,y:16}} animate={{opacity:1,y:0}} transition={{duration:.5}}
      className="glass rounded-2xl p-5">
      <div className="flex items-end justify-between mb-3">
        <div>
          <div className="text-[11px] uppercase tracking-wider text-muted">Umsatz-Ziel · 5 Monate</div>
          <div className="text-2xl font-extrabold text-white mt-1"><AnimatedNumber value={value} format={v=>eur(v)}/></div>
        </div>
        <div className="text-accent text-3xl font-extrabold">{pct}%</div>
      </div>
      <div className="h-3 rounded-full bg-[#0a0f1a] overflow-hidden border border-line">
        <motion.div initial={{width:0}} animate={{width:pct+'%'}} transition={{duration:1.1,ease:'easeOut'}}
          className="h-full rounded-full" style={{background:'linear-gradient(90deg,#1693c4,#39d6ff)'}}/>
      </div>
      <div className="text-xs text-muted mt-2">Ziel: {eur(goal)}</div>
    </motion.div>
  )
}
