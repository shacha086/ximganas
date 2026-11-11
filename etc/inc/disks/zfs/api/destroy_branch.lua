--
--	destroy_branch.lua
--
--	Part of XigmaNAS® (https://www.xigmanas.com).
--	Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
--	All rights reserved.
--
--	Redistribution and use in source and binary forms, with or without
--	modification, are permitted provided that the following conditions are met:
--
--	1. Redistributions of source code must retain the above copyright notice, this
--	   list of conditions and the following disclaimer.
--
--	2. Redistributions in binary form must reproduce the above copyright notice,
--	   this list of conditions and the following disclaimer in the documentation
--	   and/or other materials provided with the distribution.
--
--	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
--	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
--	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
--	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
--	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
--	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
--	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
--	ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
--	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
--	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
--
--	The views and conclusions contained in the software and documentation are those
--	of the authors and should not be interpreted as representing official policies
--	of XigmaNAS®, either expressed or implied.
--
-- recursively build the list of datasets, dependents first
function scan_branch(branch,leaves)
--	collect children
	for child in zfs.list.children(branch) do
		leaves = scan_branch(child,leaves)
	end
--	collect snapshots
	for snapshot in zfs.list.snapshots(branch) do
--		scan clones
		for clone in zfs.list.clones(snapshot) do
			leaves = scan_branch(clone,leaves)
		end
		table.insert(leaves,snapshot)
	end
	table.insert(leaves,branch)
	return leaves
end
--	destroy collected datasets
function destroy_datasets(datasets)
	for _,dataset in ipairs(datasets) do
		assert(zfs.sync.destroy(dataset) == 0,'destroy dataset ' .. dataset .. ' failed.')
	end
end
--	main
args = ...
argv = args["argv"]
root = argv[1]
assert(zfs.exists(root),'dataset ' .. root .. ' not found.')
datasets = scan_branch(root,{})
destroy_datasets(datasets)
