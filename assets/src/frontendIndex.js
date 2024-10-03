import 'regenerator-runtime/runtime';

import {createRoot} from 'react-dom/client';
import {TableOfContentsFrontend} from './blocks/TableOfContents/TableOfContentsFrontend';
import {HappyPointFrontend} from './blocks/HappyPoint/HappyPointFrontend';
import {MediaFrontend} from './blocks/Media/MediaFrontend';
import {ColumnsFrontend} from './blocks/Columns/ColumnsFrontend';

// Render React components
const COMPONENTS = {
  'planet4-blocks/submenu': TableOfContentsFrontend,
  'planet4-blocks/happypoint': HappyPointFrontend,
  'planet4-blocks/media-video': MediaFrontend,
  'planet4-blocks/columns': ColumnsFrontend,
};

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-render]').forEach(
    blockNode => {
      const blockName = blockNode.dataset.render;
      if (!COMPONENTS[blockName]) {
        return;
      }

      const BlockFrontend = COMPONENTS[blockName];
      if (!BlockFrontend) {
        return;
      }
      const attributes = JSON.parse(blockNode.dataset.attributes);
      const rootElement = createRoot(blockNode);
      rootElement.render(<BlockFrontend {...attributes.attributes} />);
    }
  );
});
