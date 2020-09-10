import csv
import pprint
import numpy as np

def parse(csv_file):
    """
    Takes in the csv file name of the world (csv_file::str)
    and returns the raw data of world in that csv file.
    """

    with open(csv_file, 'r') as f:

        rows = f.readlines()
        rows = list(map(lambda x:x.strip(), rows))
        rows = [r.split('\t') for r in rows]

    keys = rows[0]
    data = rows[1:]

    return {key.lower():k for k,key in enumerate(keys)}, data


def world_to_tree(world_name):
    """ 
    Takes in the name of the world (world_name::str) 
    and returns a disctionary of node information.
    """
    # s : steps from parent

    csv_file = "tree_builder/worlds/" + world_name + ".csv"
    keys, data = parse(csv_file)
    summary = {}
    
    for d in data: # store relevant information for each id
        summary[d[keys['nid']]] = {'parent': d[keys['parentid']],
                            'stepsfromroot': eval(d[keys['stepsfromroot']]),
                                  'remains': eval(d[keys['blackremains']])}

        if d[keys['parentid']] == 'NA': # store root node id
            root = d[keys['nid']]

        try: # store cell distances (exception at root node)
            summary[d[keys['nid']]]['celldistances'] = eval(d[keys['celldistances']])
        except IndexError:
            summary[d[keys['nid']]]['celldistances'] = []

    for id in summary: # probability of termination
        if summary[id]['parent'] != 'NA':
            summary[summary[id]['parent']].setdefault('children',[]).append(id)

    return summary, root


def memoize(function):
    memo = {}
    def wrapper(*args):
        if args not in memo:
            memo[args] = function(*args)
        return memo[args]
    return wrapper


@memoize
def node_value(world_name, node_id, gamma=1):
    """
    Returns value of node_id::str, discounted by 0 < gamma::float < 1. 
    """

    summary, _ = world_to_tree(world_name)

    node = summary[node_id]
    celldistances = [node['celldistances']] if isinstance(node['celldistances'], int) else node['celldistances']

    value, p_exit = 0, 0

    # first term in the discounted model
    if node['parent'] != 'NA':
        p_exit = len(celldistances)/summary[node['parent']]['remains']
        value += p_exit*( node['stepsfromroot'] + sum(celldistances)/len(celldistances) )

    # second term in the discounted model
    if node.get('children', []):
        min_child_value = float('inf')

        for child in node['children']:
            child_value = node_value(world_name, child, gamma)
            if child_value < min_child_value:
                min_child_value = child_value

        value += (1-p_exit) * gamma * min_child_value

    return value


def values_per_gamma():
    """ 
    Run this function to get output/discounted_values.csv 
    100 gamma (discount factor) values for each node in data/treeNodePolicyIncludingN=1.csv
    """

    keys, data = parse("data/treeNodePolicyIncludingN=1.csv")
    gammas = [1] + [round(0.01*i,2) for i in range(100)]

    with open('output/discounted_values.csv', mode='w') as out_file:
        out_writer = csv.writer(out_file, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
        out_writer.writerow(['world', 'node', 'child', 'value'] + ['dv_'+str(gamma) for gamma in gammas[1:]])

        for d in data:
            values = [round(node_value(d[keys['world']], d[keys['child']], gamma),3) for gamma in gammas]
            out_writer.writerow([d[keys['world']], d[keys['node']], d[keys['child']]] + values)


if __name__ == "__main__":

    pp = pprint.PrettyPrinter(compact=False, width=100)

    # pp.pprint(world_to_tree('courtyard'))

    print(node_value('cubicles', 'N4932175', 0.55))

    # values_per_gamma()