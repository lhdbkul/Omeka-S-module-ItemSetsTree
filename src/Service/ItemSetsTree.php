<?php

/*
 * Copyright 2020 BibLibre
 *
 * This file is part of ItemSetsTree.
 *
 * ItemSetsTree is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ItemSetsTree is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ItemSetsTree.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace ItemSetsTree\Service;

use Omeka\Api\Manager as ApiManager;

class ItemSetsTree
{
    protected $api;

    public function __construct(ApiManager $api)
    {
        $this->api = $api;
    }

    public function getItemSetsTree()
    {
        $itemSets = $this->api->search('item_sets')->getContent();
        $itemSetsTreeEdges = $this->api->search('item_sets_tree_edges')->getContent();

        $parentMap = [];
        foreach ($itemSetsTreeEdges as $itemSetsTreeEdge) {
            $parentMap[$itemSetsTreeEdge->itemSet()->id()] = $itemSetsTreeEdge->parentItemSet()->id();
        }

        $itemSetsMap = [];
        foreach ($itemSets as $itemSet) {
            $itemSetsMap[$itemSet->id()] = [
                'itemSet' => $itemSet,
                'children' => [],
            ];
        }

        $itemSetsTree = [];
        foreach ($itemSets as $itemSet) {
            $itemSetId = $itemSet->id();
            $parentItemSetId = $parentMap[$itemSetId] ?? null;

            if ($parentItemSetId) {
                $itemSetsMap[$parentItemSetId]['children'][] = & $itemSetsMap[$itemSetId];
            } else {
                $itemSetsTree[] = & $itemSetsMap[$itemSetId];
            }
        }

        return $itemSetsTree;
    }
}
