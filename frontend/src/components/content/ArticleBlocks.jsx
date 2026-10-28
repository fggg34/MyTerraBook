import { Link } from 'react-router-dom'
import StrokeIcon from './StrokeIcon'

/* eslint-disable react/prop-types */
export default function ArticleBlocks({ blocks = [] }) {
  return (
    <>
      {blocks.map((block, index) => {
        const key = `${block.type || 'block'}-${index}`
        if (block.type === 'heading') {
          const Tag = block.kicker === 'h3' ? 'h3' : 'h2'
          return <Tag key={key}>{block.heading}</Tag>
        }
        if (block.type === 'paragraph') return <p key={key}>{block.text}</p>
        if (block.type === 'quote') return <blockquote key={key}><p>{block.text}</p>{block.heading && <cite>{block.heading}</cite>}</blockquote>
        if (block.type === 'image') {
          return block.image ? <img key={key} src={block.image} alt={block.imageAlt || ''} loading="lazy" /> : null
        }
        if (block.type === 'list') {
          return (
            <ul key={key}>
              {(block.items || []).map((item) => <li key={item}>{item}</li>)}
            </ul>
          )
        }
        if (block.type === 'callout') {
          return (
            <div className="tb-tip" key={key}>
              <i><StrokeIcon name="sparkles" size={18} /></i>
              <div>
                {block.kicker && <small>{block.kicker}</small>}
                <p>{block.text}</p>
              </div>
            </div>
          )
        }
        if (block.type === 'day') {
          return (
            <div className="tb-day" key={key}>
              <b>{(block.kicker || '').replace(/\D/g, '') || index}</b>
              <div>
                {block.kicker && <small>{block.kicker}</small>}
                <h3>{block.heading}</h3>
                <p>{block.text}</p>
                {block.meta && <span><StrokeIcon name="route" size={13} /> {block.meta}</span>}
              </div>
            </div>
          )
        }
        if (block.type === 'route') {
          return (
            <div className="tb-route-book" key={key}>
              <div>
                {block.kicker && <small>{block.kicker}</small>}
                <h3>{block.heading}</h3>
                <p>{block.text}</p>
                {block.href && <Link className="tb-btn gold sm" to={block.href}>{block.button || 'See listings'} <StrokeIcon name="arrow" size={16} /></Link>}
              </div>
              {block.image && (
                <div className="card">
                  <img src={block.image} alt={block.imageAlt || ''} loading="lazy" />
                </div>
              )}
            </div>
          )
        }
        return null
      })}
    </>
  )
}
