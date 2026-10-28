export default function Container({ as: Tag = 'div', className = '', children, ...props }) {
  const classes = className ? `tb-container ${className}` : 'tb-container'

  return (
    <Tag className={classes} {...props}>
      {children}
    </Tag>
  )
}
