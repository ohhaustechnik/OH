import { motion } from 'framer-motion'
import AnimatedNumber from './AnimatedNumber.jsx'
const ring = { ok:'#34e09a', warn:'#e7b14b', bad:'#ff5d6c', neutral:'#1e2940' }
export default function KpiCard({ title, value, sub, status='neutral', trend, icon, delay=0, format }) {
  return (
    <motion.div initial={{opacity:0,y:16}} animate={{opacity:1,y:0}} transition={{duration:.45,delay}}
      whileHover={{y:-3}} className="glass rounded-2xl p-4 relative overflow-hidden"
      style={{borderColor:ring[status]||'#1e2940'}}>
      <div className="flex items-center justify-between">
        <span className="text-[11px] uppercase tracking-wider text-muted">{title}</span>
        {icon && <span className="text-lg opacity-80">{icon}</span>}
      </div>
      <div className="text-3xl font-extrabold text-white mt-2">
        {typeof value==='number' ? <AnimatedNumber value={value} format={format||(v=>Math.round(v))}/> : value}
      </div>
      {sub && <div className="text-xs text-muted mt-1">{sub}</div>}
      {trend && <div className="text-xs mt-1" style={{color: trend.startsWith('-')?'#ff5d6c':'#34e09a'}}>{trend.startsWith('-')?'▼':'▲'} {trend}</div>}
    </motion.div>
  )
}
