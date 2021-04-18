import { VarPicker } from './VarPicker';

export const renderSelectedVars = (rootElement, cssVars = [], lastTarget, groups, rawGroups, allVars) => {
  wp.element.render(
    <VarPicker
      initialOpen={ false }
      selectedVars={ cssVars }
      groups={ groups }
      rawGroups={rawGroups}
      lastTarget={ lastTarget }
      allVars={ allVars }
    />,
    rootElement
  );
};
