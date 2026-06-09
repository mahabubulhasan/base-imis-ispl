/* tabs.js - Web Components for Custom Tab Interface */

class TabContainer extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.shadowRoot.innerHTML = `
      <style>
        :host {
          display: block;
          width: 100%;
          
          /* Default Modern Minimalist Design Tokens */
          --tabs-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          --tabs-bg: transparent;
          
          /* Text and States */
          --tabs-text-color: #64748b;
          --tabs-text-hover-color: #0f172a;
          --tabs-text-active-color: #0284c7;
          --tabs-text-disabled-color: #cbd5e1;
          
          /* Active Indicator */
          --tabs-indicator-color: #0284c7;
          --tabs-indicator-height: 2px;
          --tabs-indicator-radius: 9999px;
          
          /* Layout and Spacing */
          --tabs-nav-border-color: #e2e8f0;
          --tabs-nav-border-width: 1px;
          --tabs-button-padding: 0.75rem 1rem;
          --tabs-button-gap: 0.5rem;
          --tabs-panel-padding: 1.5rem 0;
          
          /* Typography */
          --tabs-font-size: 0.875rem;
          --tabs-font-weight: 500;
          --tabs-active-font-weight: 600;
          
          /* Animations */
          --tabs-transition-duration: 0.3s;
          --tabs-transition-timing: cubic-bezier(0.4, 0, 0.2, 1);
          
          /* Focus rings for Accessibility */
          --tabs-focus-ring-color: rgba(2, 132, 199, 0.4);
          --tabs-focus-ring-width: 3px;
        }

        @media (prefers-color-scheme: dark) {
          :host {
            --tabs-text-color: #94a3b8;
            --tabs-text-hover-color: #f1f5f9;
            --tabs-text-active-color: #38bdf8;
            --tabs-text-disabled-color: #475569;
            
            --tabs-indicator-color: #38bdf8;
            --tabs-nav-border-color: #334155;
            --tabs-focus-ring-color: rgba(56, 189, 248, 0.4);
          }
        }
      </style>
      <slot name="navigation"></slot>
      <slot name="content"></slot>
    `;
  }

  connectedCallback() {
    this.addEventListener('tab-select', this._onTabSelect.bind(this));
    // Initialize initial active state
    requestAnimationFrame(() => {
      this._initializeTabs();
    });
  }

  _initializeTabs() {
    const buttons = this.querySelectorAll('tab-button');
    const activeBtn = Array.from(buttons).find(b => b.hasAttribute('active') && !b.hasAttribute('disabled'));
    
    if (activeBtn) {
      this._activateTab(activeBtn.getAttribute('target'));
    } else if (buttons.length > 0) {
      // Find first non-disabled button to activate
      const firstEnabled = Array.from(buttons).find(b => !b.hasAttribute('disabled'));
      if (firstEnabled) {
        this._activateTab(firstEnabled.getAttribute('target'));
      }
    }
  }

  _activateTab(targetId) {
    if (!targetId) return;

    const buttons = this.querySelectorAll('tab-button');
    const panels = this.querySelectorAll('tab-panel');

    // Update buttons
    buttons.forEach(btn => {
      if (btn.getAttribute('target') === targetId) {
        btn.setAttribute('active', '');
      } else {
        btn.removeAttribute('active');
      }
    });

    // Update panels
    panels.forEach(panel => {
      if (panel.getAttribute('id') === targetId) {
        panel.setAttribute('active', '');
      } else {
        panel.removeAttribute('active');
      }
    });

    // Fire event for external consumers
    this.dispatchEvent(new CustomEvent('tab-change', {
      detail: { activeId: targetId },
      bubbles: true,
      composed: true
    }));
  }

  _onTabSelect(event) {
    const targetId = event.detail.target;
    this._activateTab(targetId);
  }
}

class TabNavigation extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.shadowRoot.innerHTML = `
      <style>
        :host {
          display: block;
          width: 100%;
        }
        .nav-container {
          position: relative;
          border-bottom: var(--tabs-nav-border-width, 1px) solid var(--tabs-nav-border-color, #e2e8f0);
          background: var(--tabs-bg, transparent);
        }
        .nav-scroll {
          display: flex;
          gap: var(--tabs-button-gap, 0.5rem);
          position: relative;
          overflow-x: auto;
          scrollbar-width: none; /* Firefox */
          scroll-behavior: smooth;
        }
        .nav-scroll::-webkit-scrollbar {
          display: none; /* Chrome, Safari, Opera */
        }
        .indicator {
          position: absolute;
          bottom: 0;
          left: 0;
          height: var(--tabs-indicator-height, 2px);
          background-color: var(--tabs-indicator-color, #0284c7);
          border-radius: var(--tabs-indicator-radius, 9999px);
          width: 0;
          transform: translateX(0);
          transition: transform var(--tabs-transition-duration, 0.3s) var(--tabs-transition-timing, cubic-bezier(0.4, 0, 0.2, 1)),
                      width var(--tabs-transition-duration, 0.3s) var(--tabs-transition-timing, cubic-bezier(0.4, 0, 0.2, 1));
          pointer-events: none;
        }
      </style>
      <div class="nav-container" part="nav-container">
        <div class="nav-scroll" role="tablist" part="nav-scroll">
          <slot></slot>
          <div class="indicator" part="indicator"></div>
        </div>
      </div>
    `;

    this._onResize = this._onResize.bind(this);
    this._onKeyDown = this._onKeyDown.bind(this);
  }

  connectedCallback() {
    this._setupObserver();
    
    // Keydown for accessibility keyboard navigation
    this.shadowRoot.querySelector('.nav-scroll').addEventListener('keydown', this._onKeyDown);
    
    // Resize listener for layout updating
    window.addEventListener('resize', this._onResize);

    // Initial update
    requestAnimationFrame(() => {
      this.updateIndicator();
    });
  }

  disconnectedCallback() {
    if (this._observer) {
      this._observer.disconnect();
    }
    window.removeEventListener('resize', this._onResize);
    this.shadowRoot.querySelector('.nav-scroll').removeEventListener('keydown', this._onKeyDown);
  }

  _setupObserver() {
    this._observer = new MutationObserver(() => {
      this.updateIndicator();
    });
    // Watch for tab-button modifications or active attribute changes
    this._observer.observe(this, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['active']
    });
  }

  _onResize() {
    // Disable transition during window resize to prevent laggy dragging visual
    const indicator = this.shadowRoot.querySelector('.indicator');
    indicator.style.transition = 'none';
    
    this.updateIndicator();
    
    // Restore transitions on next frame
    requestAnimationFrame(() => {
      indicator.style.transition = '';
    });
  }

  _onKeyDown(event) {
    const buttons = Array.from(this.querySelectorAll('tab-button:not([disabled])'));
    if (buttons.length === 0) return;

    const activeElement = document.activeElement;
    let currentIndex = buttons.indexOf(activeElement);

    if (currentIndex === -1) return;

    let nextIndex = currentIndex;
    let preventDefault = false;

    switch (event.key) {
      case 'ArrowRight':
        nextIndex = (currentIndex + 1) % buttons.length;
        preventDefault = true;
        break;
      case 'ArrowLeft':
        nextIndex = (currentIndex - 1 + buttons.length) % buttons.length;
        preventDefault = true;
        break;
      case 'Home':
        nextIndex = 0;
        preventDefault = true;
        break;
      case 'End':
        nextIndex = buttons.length - 1;
        preventDefault = true;
        break;
    }

    if (preventDefault) {
      event.preventDefault();
      const targetBtn = buttons[nextIndex];
      targetBtn.focus();
      targetBtn.click();
    }
  }

  updateIndicator() {
    const buttons = this.querySelectorAll('tab-button');
    let activeBtn = null;
    for (const btn of buttons) {
      if (btn.hasAttribute('active')) {
        activeBtn = btn;
        break;
      }
    }

    const indicator = this.shadowRoot.querySelector('.indicator');
    if (!activeBtn) {
      indicator.style.width = '0px';
      return;
    }

    const navScroll = this.shadowRoot.querySelector('.nav-scroll');
    const containerRect = navScroll.getBoundingClientRect();
    const btnRect = activeBtn.getBoundingClientRect();

    // Calculate position relative to container
    const left = btnRect.left - containerRect.left + navScroll.scrollLeft;
    const width = btnRect.width;

    indicator.style.width = `${width}px`;
    indicator.style.transform = `translateX(${left}px)`;

    // Automatically scroll the tab into view if container is overflowing
    activeBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
  }
}

class TabButton extends HTMLElement {
  static get observedAttributes() { return ['active', 'disabled']; }

  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.shadowRoot.innerHTML = `
      <style>
        :host {
          display: inline-block;
          outline: none;
        }
        button {
          font-family: var(--tabs-font-family, sans-serif);
          font-size: var(--tabs-font-size, 0.875rem);
          font-weight: var(--tabs-font-weight, 500);
          color: var(--tabs-text-color, #64748b);
          background: transparent;
          border: none;
          padding: var(--tabs-button-padding, 0.75rem 1rem);
          cursor: pointer;
          position: relative;
          transition: color var(--tabs-transition-duration, 0.3s) var(--tabs-transition-timing, cubic-bezier(0.4, 0, 0.2, 1)),
                      font-weight var(--tabs-transition-duration, 0.3s) var(--tabs-transition-timing, cubic-bezier(0.4, 0, 0.2, 1));
          outline: none;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: var(--tabs-button-gap, 0.5rem);
          width: 100%;
          box-sizing: border-box;
          white-space: nowrap;
        }
        button:hover:not(:disabled) {
          color: var(--tabs-text-hover-color, #0f172a);
        }
        :host([active]) button {
          color: var(--tabs-text-active-color, #0284c7);
          font-weight: var(--tabs-active-font-weight, 600);
        }
        button:focus-visible {
          outline: none;
          box-shadow: 0 0 0 var(--tabs-focus-ring-width, 3px) var(--tabs-focus-ring-color, rgba(2, 132, 199, 0.4));
          border-radius: 4px;
        }
        button:disabled {
          color: var(--tabs-text-disabled-color, #cbd5e1);
          cursor: not-allowed;
        }
      </style>
      <button type="button" part="button" role="tab" aria-selected="false">
        <slot></slot>
      </button>
    `;
  }

  connectedCallback() {
    this.shadowRoot.querySelector('button').addEventListener('click', this._onClick.bind(this));
    
    // Sync initial states
    const btn = this.shadowRoot.querySelector('button');
    const isActive = this.hasAttribute('active');
    const isDisabled = this.hasAttribute('disabled');
    
    btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
    btn.setAttribute('tabindex', isActive && !isDisabled ? '0' : '-1');
    if (isDisabled) btn.setAttribute('disabled', '');
  }

  attributeChangedCallback(name, oldValue, newValue) {
    const button = this.shadowRoot.querySelector('button');
    if (!button) return;

    if (name === 'active') {
      const isActive = newValue !== null;
      button.setAttribute('aria-selected', isActive ? 'true' : 'false');
      button.setAttribute('tabindex', isActive && !this.hasAttribute('disabled') ? '0' : '-1');
    } else if (name === 'disabled') {
      const isDisabled = newValue !== null;
      if (isDisabled) {
        button.setAttribute('disabled', '');
        button.setAttribute('tabindex', '-1');
      } else {
        button.removeAttribute('disabled');
        button.setAttribute('tabindex', this.hasAttribute('active') ? '0' : '-1');
      }
    }
  }

  focus() {
    this.shadowRoot.querySelector('button').focus();
  }

  _onClick(event) {
    if (this.hasAttribute('disabled')) {
      event.preventDefault();
      return;
    }
    this.dispatchEvent(new CustomEvent('tab-select', {
      detail: { target: this.getAttribute('target') },
      bubbles: true,
      composed: true
    }));
  }
}

class TabContent extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.shadowRoot.innerHTML = `
      <style>
        :host {
          display: block;
          width: 100%;
        }
      </style>
      <slot></slot>
    `;
  }
}

class TabPanel extends HTMLElement {
  static get observedAttributes() { return ['active']; }

  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.shadowRoot.innerHTML = `
      <style>
        :host {
          display: none;
          opacity: 0;
          transform: translateY(8px);
          transition: opacity var(--tabs-transition-duration, 0.3s) var(--tabs-transition-timing, cubic-bezier(0.4, 0, 0.2, 1)),
                      transform var(--tabs-transition-duration, 0.3s) var(--tabs-transition-timing, cubic-bezier(0.4, 0, 0.2, 1));
          width: 100%;
          box-sizing: border-box;
          padding: var(--tabs-panel-padding, 1.5rem 0);
        }
        :host([active]) {
          display: block;
        }
        :host(.visible) {
          opacity: 1;
          transform: translateY(0);
        }
      </style>
      <div role="tabpanel" part="panel">
        <slot></slot>
      </div>
    `;
  }

  connectedCallback() {
    if (this.hasAttribute('active')) {
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          this.classList.add('visible');
        });
      });
    }
  }

  attributeChangedCallback(name, oldValue, newValue) {
    if (name === 'active') {
      const isActive = newValue !== null;
      if (isActive) {
        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            this.classList.add('visible');
          });
        });
      } else {
        this.classList.remove('visible');
      }
    }
  }
}

// Register Custom Elements
customElements.define('tab-container', TabContainer);
customElements.define('tab-navigation', TabNavigation);
customElements.define('tab-button', TabButton);
customElements.define('tab-content', TabContent);
customElements.define('tab-panel', TabPanel);
