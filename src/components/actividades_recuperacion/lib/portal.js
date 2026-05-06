export function portal(node) {
  let portalEl = document.getElementById('modal-portal')
  
  if (!portalEl) {
    portalEl = document.createElement('div')
    portalEl.id = 'modal-portal'
    portalEl.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:50;'
    document.body.appendChild(portalEl)
  }
  
  portalEl.appendChild(node)
  node.style.pointerEvents = 'auto'
  
  return {
    destroy() {
      node.remove()
    }
  }
}
