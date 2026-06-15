import { useRef } from 'react'
import useDragScroll from '../../hooks/useDragScroll'
import useSectionReveal from '../../hooks/useSectionReveal'
import HostPhoto from './HostPhoto'

const VARIANT_CLASS = {
  clay: 'accent-green',
  moss: 'accent-blue',
  ochre: 'accent-blue',
  espresso: 'accent-navy',
}

function ProofPair({ pair, duplicate = false }) {
  return (
    <>
      <div className="host-proof-tall" aria-hidden={duplicate || undefined}>
        <HostPhoto src={pair.tall.image} alt={pair.tall.name} />
        <div className="host-proof-meta">
          <div className="host-proof-name">{pair.tall.name}</div>
          <div className="host-proof-role">{pair.tall.role}</div>
        </div>
      </div>
      <div className="host-proof-stack" aria-hidden={duplicate || undefined}>
        {pair.stack.map((item) =>
          item.type === 'stat' ? (
            <div
              key={`${item.big}-${duplicate}`}
              className={`host-proof-sq host-proof-stat ${VARIANT_CLASS[item.variant] ?? 'accent-green'}`}
            >
              <div className="host-proof-stat-big">{item.big}</div>
              <div className="host-proof-stat-desc">{item.desc}</div>
            </div>
          ) : (
            <div key={`${item.name}-${duplicate}`} className="host-proof-sq host-proof-photo">
              <HostPhoto src={item.image} alt={item.name} />
              <div className="host-proof-meta">
                <div className="host-proof-name">{item.name}</div>
                <div className="host-proof-role">{item.role}</div>
              </div>
            </div>
          ),
        )}
      </div>
    </>
  )
}

export default function HostProofMarquee({ stats = [] }) {
  const sectionRef = useRef(null)
  const marqueeRef = useRef(null)
  useSectionReveal(sectionRef, { revealDoneMs: 1200, threshold: 0.08 })

  useDragScroll(marqueeRef, {
    enabled: stats.length > 0,
    convertAnimationFrom: '.host-proof-track',
  })

  if (!stats.length) return null

  return (
    <section className="host-proof" ref={sectionRef}>
      <div className="host-proof-marquee" ref={marqueeRef}>
        <div className="host-proof-track">
          {stats.map((pair) => (
            <ProofPair key={pair.tall.name} pair={pair} />
          ))}
          {stats.map((pair) => (
            <ProofPair key={`${pair.tall.name}-dup`} pair={pair} duplicate />
          ))}
        </div>
      </div>
    </section>
  )
}
