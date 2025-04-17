import re
import sys


def getFunctionList(file):
    f = open(file, "r")
    return {l for l in re.findall(r"(?<=function\s)\w+(?=\([\s,(\$\w+)]*\))", f.read())}


def getFunctionCalls(file):
    f = open(file, "r")
    return {l for l in re.findall(r"\w+(?=\()", f.read())}


def getBuildInFuncPHP():
    f = open("./tests/php_build_in_func", "r")
    return {l.replace("\n", "") for l in f.readlines()}


def getFunctDifference(source_file, test_file):
    func = getFunctionList(source_file)
    calls = getFunctionCalls(test_file)

    buid_in_func = getBuildInFuncPHP()

    return (func - buid_in_func) - calls


def main():

    if len(sys.argv) == 2 + 1:
        # print(f"Difference of {sys.argv[1]} - {sys.argv[2]}")
        for l in getFunctDifference(sys.argv[1], sys.argv[2]):
            print(l)
    else:
        print()


if __name__ == "__main__":
    main()
