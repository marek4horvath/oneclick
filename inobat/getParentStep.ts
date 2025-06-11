const getParentStep = (parents: any[]): string => {
    if (!Array.isArray(parents) || parents.length === 0) {
        return "";
    }

    const currentNames = parents.map((parent) => parent.name || "").join(", ");

    const ancestorNames = parents
        .map((parent) => parent?.parent ? getParentStep(parent.parent) : "")
        .filter((name) => name)
        .join(", ");
    
    return ancestorNames ? `${ancestorNames}, ${currentNames}` : currentNames;
}